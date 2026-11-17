<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DailyVerseService;

class DailyVerseController extends Controller
{
    public function __invoke(DailyVerseService $service)
    {
        $verse = $service->getRandom();

        if (!$verse) {
            return response()->json(['error' => 'Стихи дня не найдены'], 404);
        }

        return response()->json($verse);
    }
}
