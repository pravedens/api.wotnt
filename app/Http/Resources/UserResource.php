<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'two_factor_enabled' => ! is_null($this->two_factor_secret),
            'two_factor_confirmed' => ! is_null($this->two_factor_confirmed_at),

            // ✅ Роли: массив объектов { value, label }
            'roles' => $this->getRoleNames()
                ->map(fn (string $name) => [
                    'value' => $name,
                    'label' => config("roles.labels.{$name}", $name),
                ])
                ->values(),

            // Дополнительные данные профиля
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'phone' => $this->phone,
            'city' => $this->city,
            'church_name' => $this->church_name,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'about' => $this->about,
            'marital_status' => $this->marital_status,
            'gender' => $this->gender,
            'avatar_url' => $this->avatar_url,
            'consent_pd' => $this->consent_pd,
            'can_upload_avatar' => $this->can_upload_avatar,
        ];
    }

    public static $wrap = null;
}
