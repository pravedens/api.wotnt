<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GroupController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $query = Group::query();

        // Считаем количество проповедей с учетом активных фильтров
        $query->withCount(['posts' => function ($query) use ($request) {
            // Если выбран спикер, считаем только его проповеди
            if ($request->filled('category')) {
                $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
            }
            // Если выбрано мероприятие, считаем только его проповеди
            if ($request->filled('conference')) {
                $query->whereHas('conference', fn($q) => $q->where('slug', $request->conference));
            }
            // Фильтр по поиску (если нужно)
            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }
            // ВАЖНО: фильтр по самому себе (году) не применяем,
            // чтобы видеть, сколько проповедей будет в других годах
        }]);

        return GroupResource::collection(
            $query->orderByDesc('title')->get() // Сортировка по убыванию (2026, 2025...)
        );
    }
}
