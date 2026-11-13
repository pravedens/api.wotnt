<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostCommentRequest;
use App\Http\Requests\UpdatePostCommentRequest;
use App\Http\Resources\PostCommentResource;
use App\Models\CommentLike;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Создать комментарий (или ответ на комментарий).
     * Доступно только авторизованным, с verified email и consent_pd = true
     * (проверяется middleware + правилом в StorePostCommentRequest).
     */
    public function store(StorePostCommentRequest $request, Post $post): JsonResponse
    {
        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $request->input('parent_id'),
            'content' => $request->input('content'),
            // is_approved = true по умолчанию в миграции
        ]);

        $comment->load('user');

        return response()->json([
            'message' => 'Комментарий добавлен.',
            'comment' => PostCommentResource::make($comment)->resolve(),
        ], 201);
    }

    /**
     * Лайк / снятие лайка с комментария.
     * Доступно только авторизованным.
     */
    public function toggleLike(Request $request, PostComment $comment): JsonResponse
    {
        $user = $request->user();

        $like = CommentLike::where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            $like->delete();
            $comment->decrement('likes_count');

            return response()->json([
                'liked' => false,
                'count' => $comment->likes_count,
            ]);
        }

        CommentLike::create([
            'comment_id' => $comment->id,
            'user_id' => $user->id,
        ]);
        $comment->increment('likes_count');

        return response()->json([
            'liked' => true,
            'count' => $comment->likes_count,
        ]);
    }

    public function update(UpdatePostCommentRequest $request, PostComment $comment): JsonResponse
    {
        $comment->update([
            'content' => $request->input('content'),
            'is_approved' => false, // опционально: комментарий уходит на повторную модерацию
        ]);

        $comment->load('user');

        return response()->json([
            'message' => 'Комментарий обновлён.',
            'comment' => PostCommentResource::make($comment)->resolve(),
        ]);
    }

    public function destroy(Request $request, PostComment $comment): JsonResponse
    {
        $user = $request->user();

        if ($comment->user_id !== $user->id && ! $user->hasRole('super_admin')) {
            return response()->json([
                'message' => 'Вы можете удалять только свои комментарии.',
                'code' => 'FORBIDDEN',
            ], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Комментарий удалён.']);
    }
}
