<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\HomepageSetting;
use App\Models\User; // ✅ Импортируем настройки
use Illuminate\Http\Request;

class HomepageController extends Controller
{
    public function index(Request $request)
    {
        $todayStr = now()->format('m-d');
        $holiday = Holiday::where(function ($q) use ($todayStr) {
            // Ищем по строке MM-DD
            $q->where('is_annual', true)
                ->whereRaw("DATE_FORMAT(date, '%m-%d') = ?", [$todayStr]);
        })->orWhere(function ($q) {
            // Или обычные (не ежегодные)
            $q->where('is_annual', false)
                ->whereDate('date', '<=', now())
                ->where(function ($sub) {
                    $sub->whereDate('end_date', '>=', now())->orWhereNull('end_date');
                });
        })->first();

        $data = [
            'holiday' => $holiday ? [
                'title' => $holiday->title,
                'date' => $holiday->date->format('d.m.Y'),
                'description' => $holiday->description,
                'image_url' => $holiday->image_url,
                'styles' => [
                    'text_color' => $holiday->text_color,
                    'bg_color' => $holiday->bg_color,
                    'font_family' => $holiday->font_family,
                    'title_font_family' => $holiday->title_font_family,
                ],
            ] : null,
            'birthdays' => [],
            'deceased' => [],
            // ✅ Передаем стили для целых блоков (если настроек нет, ставим дефолт)
            'settings' => [
                'birthdays' => HomepageSetting::get('birthdays') ?? (object) [
                    'title' => '🎉 Дни рождения в этом месяце',
                    'greeting_text' => 'Поздравляем наших дорогих братьев и сестер!',
                    'bg_color' => '#2e1065', // Темно-фиолетовый
                    'text_color' => '#ffffff',
                    'font_family' => 'sans-serif',
                ],
                'deceased' => HomepageSetting::get('deceased') ?? (object) [
                    'title' => '🕊️ Помним и любим',
                    'greeting_text' => 'Светлая память о наших ушедших братьях и сестрах.',
                    'bg_color' => '#111827', // Темно-серый
                    'text_color' => '#d1d5db',
                    'font_family' => 'serif',
                ],
            ],
        ];

        // 2 и 3. Только для авторизованных и с согласием
        if ($request->user() && $request->user()->consent_pd) {

            // Дни рождения (БЕЗ индивидуальных стилей)
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
