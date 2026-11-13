<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * Список спикеров (категорий) для фильтра.
     */
    public function __invoke(Request $request): AnonymousResourceCollection
{
    $query = Category::query();

    $query->withCount(['posts' => function ($query) use ($request) {
        if ($request->filled('group')) {
            $query->whereHas('group', fn($q) => $q->where('slug', $request->group));
        }
        if ($request->filled('conference')) {
            $query->whereHas('conference', fn($q) => $q->where('slug', $request->conference));
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
    }]);

    return CategoryResource::collection(
        $query->orderBy('title')->get()
    );
}
}
