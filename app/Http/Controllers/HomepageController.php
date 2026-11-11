<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\HomepageSetting;
use App\Models\User;
use Illuminate\Http\Request;

class HomepageController extends Controller
{
    public function index(Request $request)
    {
        // 1. Праздники
        $today = now();

        $holiday = Holiday::where(function ($query) use ($today) {
            // Условие 1: Ежегодные праздники (игнорируем год)
            $query->where('is_annual', true)
                ->where(function ($q) use ($today) {
                    // Если это период (есть end_date)
                    $q->whereNotNull('end_date')
                        ->whereRaw("DATE_FORMAT(date, '%m-%d') <= ?", [$today->format('m-d')])
                        ->whereRaw("DATE_FORMAT(end_date, '%m-%d') >= ?", [$today->format('m-d')]);
                })
                ->orWhere(function ($q) use ($today) {
                    // Если это один день (end_date пустой)
                    $q->whereNull('end_date')
                        ->whereRaw("DATE_FORMAT(date, '%m-%d') = ?", [$today->format('m-d')]);
                });
        })
        ->orWhere(function ($query) use ($today) {
            // Условие 2: Обычные праздники (только для текущего года)
            $query->where('is_annual', false)
                ->whereDate('date', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereDate('end_date', '>=', $today)
                        ->orWhereNull('end_date');
                });
        })
        ->first();

        // ✅ Определяем, может ли пользователь видеть личные блоки
        // (только если он авторизован и дал согласие)
        $showPersonalBlocks = $request->user() && $request->user()->consent_pd;

        $data = [
            'holiday' => $holiday ? [
                'title' => $holiday->title,
                'date' => $holiday->is_annual
                    ? $holiday->date->copy()->year(now()->year)->format('d.m.Y')
                    : $holiday->date->format('d.m.Y'),
                'end_date' => $holiday->end_date
                    ? ($holiday->is_annual
                        ? $holiday->end_date->copy()->year(now()->year)->format('d.m.Y')
                        : $holiday->end_date->format('d.m.Y'))
                    : null,
                'description' => $holiday->description,
                'image_url' => $holiday->image_url,
                'styles' => [
                    'text_color' => $holiday->text_color,
                    'bg_color' => $holiday->bg_color,
                    'font_family' => $holiday->font_family,
                    'title_font_family' => $holiday->title_font_family,
                ],
            ] : null,

            // ✅ Передаем флаг на фронтенд
            'show_personal_blocks' => $showPersonalBlocks,

            // Инициализируем пустые массивы (они заполнятся ниже, если есть права)
            'birthdays' => [],
            'deceased' => [],

            'settings' => [
                'birthdays' => HomepageSetting::get('birthdays') ?? (object) [
                    'title' => '🎉 Дни рождения в этом месяце',
                    'greeting_text' => 'Поздравляем наших дорогих братьев и сестер!',
                    'bg_color' => '#2e1065',
                    'text_color' => '#ffffff',
                    'font_family' => 'sans-serif',
                ],
                'deceased' => HomepageSetting::get('deceased') ?? (object) [
                    'title' => '🕊️ Помним и любим',
                    'greeting_text' => 'Светлая память о наших ушедших братьях и сестрах.',
                    'bg_color' => '#111827',
                    'text_color' => '#d1d5db',
                    'font_family' => 'serif',
                ],
            ],
        ];

        // 2 и 3. Заполняем данные, только если есть права
        if ($showPersonalBlocks) {
            // Дни рождения
            $birthdays = User::whereMonth('birth_date', now()->month)
                ->where('is_deceased', false)
                ->whereNotNull('birth_date')
                ->get();

            $data['birthdays'] = $birthdays->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'avatar_url' => $u->avatar_url,
            ]);

            // Ушедшие
            $deceased = User::where('is_deceased', true)
                ->where(function ($query) {
                    $query->whereDate('death_date', now())
                        ->orWhere(function ($q) {
                            $q->whereNotNull('funeral_date')
                                ->whereDate('funeral_date', '>=', now());
                        });
                })
                ->get();

            $data['deceased'] = $deceased->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'avatar_url' => $u->avatar_url,
                'death_date' => $u->death_date?->format('d.m.Y'),
                'funeral_date' => $u->funeral_date?->format('d.m.Y'),
            ]);
        }

        return response()->json($data);
    }
}
