<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Http\Request;

class HomepageController extends Controller
{
    public function index(Request $request)
    {
        // 1. Праздники (видят все)
        // Ищем праздник, который был недавно (за последние 7 дней)
        $holiday = Holiday::where('date', '>=', now()->subDays(7))
            ->orderBy('date', 'asc')
            ->first();

        $data = [
            'holiday' => $holiday ? [
                'title' => $holiday->title,
                'date' => $holiday->date->format('d.m.Y'),
                'description' => $holiday->description,
                'image_url' => $holiday->image_url,
            ] : null,
            'birthdays' => [],
            'deceased' => [],
        ];

        // 2 и 3. Только для авторизованных и с согласием
        if ($request->user() && $request->user()->consent_pd) {

            // Дни рождения в текущем месяце (исключаем ушедших)
            $birthdays = User::whereMonth('birth_date', now()->month)
                ->where('is_deceased', false)
                ->whereNotNull('birth_date')
                ->get();

            $data['birthdays'] = $birthdays->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'avatar_url' => $u->avatar_url, // Используем ваш аксессор
            ]);

            // Ушедшие из жизни (за все время)
            $deceased = User::where('is_deceased', true)
                ->get();

            $data['deceased'] = $deceased->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'avatar_url' => $u->avatar_url, // Используем ваш аксессор
            ]);
        }

        return response()->json($data);
    }
}
