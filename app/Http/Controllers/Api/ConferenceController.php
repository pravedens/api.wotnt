<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConferenceResource;
use App\Models\Conference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConferenceController extends Controller
{
    /**
     * Список мероприятий (конференций) для фильтра.
     */
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $query = Conference::query();

        $query->withCount(['posts' => function ($query) use ($request) {
            if ($request->filled('category')) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
            }
            if ($request->filled('group')) {
                $query->whereHas('group', fn ($q) => $q->where('slug', $request->group));
            }
            if ($request->filled('search')) {
                $query->where('title', 'like', '%'.$request->search.'%');
            }
        }]);

        return ConferenceResource::collection(
            $query->orderBy('title')->get()
        );
    }
}
