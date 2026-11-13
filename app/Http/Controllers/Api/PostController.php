<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Favorite;
use App\Models\Post;
use App\Models\PostStat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Список проповедей с фильтрами и поиском.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Post::with(['category', 'conference', 'group', 'author']);

        // Умный фильтр по спикеру (category), году (group), мероприятию (conference)
        $query->when($request->filled('category'), fn ($q) => $q->whereHas(
            'category', fn ($q) => $q->where('slug', $request->input('category'))
        ));

        $query->when($request->filled('group'), fn ($q) => $q->whereHas(
            'group', fn ($q) => $q->where('slug', $request->input('group'))
        ));

        $query->when($request->filled('conference'), fn ($q) => $q->whereHas(
            'conference', fn ($q) => $q->where('slug', $request->input('conference'))
        ));

        // Поиск по названию и краткому описанию
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->input('search');
            $q->where('title', 'like', "%{$search}%");
        });

        $posts = $query->latest()->paginate(8)->withQueryString();

        // Проставляем is_liked для авторизованного
        if ($user) {
            $likedPostIds = PostStat::whereIn('post_id', $posts->pluck('id'))
                ->where('user_id', $user->id)
                ->where('liked', true)
                ->pluck('post_id')
                ->flip();

            $posts->each(function (Post $post) use ($likedPostIds): void {
                $post->is_liked = $likedPostIds->has($post->id);
            });

        }

        return PostResource::collection($posts);
    }

    /**
     * Список проповедей на главной странице
     */
    public function latest(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $limit = (int) $request->input('limit', 4);
        $limit = max(1, min($limit, 20)); // защита от дурака

        $posts = Post::with(['category', 'conference', 'group', 'author'])
            ->latest()
            ->limit($limit)
            ->get();

        // Синхронизация лайков для авторизованных (как в index)
        if ($user) {
            $likedPostIds = PostStat::whereIn('post_id', $posts->pluck('id'))
                ->where('user_id', $user->id)
                ->where('liked', true)
                ->pluck('post_id')
                ->flip();

            $posts->each(function (Post $post) use ($likedPostIds): void {
                $post->is_liked = $likedPostIds->has($post->id);
            });
        }

        return PostResource::collection($posts);
    }

    /**
     * Просмотр одной проповеди.
     * Аудио и файл текста отдаются только пользователям с consent_pd = true.
     */
    public function show(Request $request, Post $post): PostResource
    {
        $post->load([
            'category',
            'conference',
            'group',
            'author',
            'comments.user',
            'comments.replies.user',
        ]);

        $user = $request->user();

        // Проверка избранного (только для авторизованных)
        $post->is_favorited = $user
            ? $post->favorites()->where('user_id', $user->id)->exists()
            : false;

        // Проверка лайка (по user_id для авторизованных, по IP для гостей)
        $post->is_liked = $user
    ? PostStat::where('post_id', $post->id)
        ->where('user_id', $user->id)
        ->where('liked', true)
        ->exists()
    : false;

        return new PostResource($post);
    }

    /**
     * Регистрация просмотра.
     * Защита от накрутки: 1 просмотр в день на user_id (или на IP для гостя).
     */
    public function registerView(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'fingerprint' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user('sanctum');
        $ip = $request->ip();

        abort_if(! is_string($ip) || $ip === '', 422, 'Не удалось определить IP посетителя.');

        $userAgent = mb_substr($request->userAgent() ?: 'unknown', 0, 500);
        $fingerprint = $validated['fingerprint'] ?? null;

        $viewsCount = DB::transaction(function () use ($post, $user, $ip, $userAgent, $fingerprint): int {
            $lockedPost = Post::query()->whereKey($post->id)->lockForUpdate()->firstOrFail();

            $now = now();
            $date = $now->toDateString();

            $query = PostStat::query()
                ->where('post_id', $lockedPost->id)
                ->where('viewed_at_date', $date);

            if ($user !== null) {
                $query->where('user_id', $user->id);
            } else {
                $query->whereNull('user_id')->where('ip', $ip);
            }

            $stat = $query->lockForUpdate()->first();

            if ($stat === null) {
                PostStat::create([
                    'post_id' => $lockedPost->id,
                    'user_id' => $user?->id,
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'fingerprint' => $fingerprint,
                    'viewed' => true,
                    'viewed_at' => $now,
                    'viewed_at_date' => $date,
                ]);

                $lockedPost->increment('views_count');
            } elseif (! $stat->viewed) {
                $stat->update([
                    'viewed' => true,
                    'viewed_at' => $now,
                ]);

                $lockedPost->increment('views_count');
            }

            return (int) $lockedPost->views_count;
        }, 3);

        return response()->json(['views_count' => $viewsCount]);
    }

    /**
     * Лайк / снятие лайка.
     * Работает и для гостей (по IP), и для авторизованных (по user_id).
     */
    public function toggleLike(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $stat = PostStat::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($stat && $stat->liked) {
            $stat->update(['liked' => false]);
            $post->decrement('likes_count');

            return response()->json(['liked' => false, 'count' => $post->likes_count]);
        }

        if ($stat) {
            $stat->update(['liked' => true]);
        } else {
            PostStat::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent() ?? 'unknown',
                'fingerprint' => $request->input('fingerprint'),
                'liked' => true,
            ]);
        }

        $post->increment('likes_count');

        return response()->json(['liked' => true, 'count' => $post->likes_count]);
    }

    /**
     * Добавить / убрать из избранного.
     * Только для авторизованных.
     */
    public function toggleFavorite(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json(['favorited' => false]);
        }

        Favorite::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        return response()->json(['favorited' => true]);
    }

    public function favorites(Request $request): AnonymousResourceCollection
    {
        $posts = Post::whereHas('favorites', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with(['category', 'conference', 'group', 'author'])
            ->latest()
            ->paginate(8);

        return PostResource::collection($posts);
    }
}
