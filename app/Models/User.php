<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'last_name', 'middle_name', 'phone', 'city', 'church_name',
    'birth_date', 'about', 'marital_status', 'gender',
    'avatar_path', 'consent_pd', 'consent_pd_at', 'consent_pd_ip', ])]
#[Hidden(['password', 'remember_token', 'consent_pd_ip'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'consent_pd' => 'boolean',
            'consent_pd_at' => 'datetime',
        ];
    }

    /**
     * Полный URL аватара (S3)
     */
    protected function avatarUrl(): Attribute
{
    return Attribute::make(
        get: function () {
            if (! $this->avatar_path) {
                return null;
            }

            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = \Illuminate\Support\Facades\Storage::disk('yandex');

            return $disk->url($this->avatar_path);
        },
    );
}

    /**
     * Признак того, что профиль заполнен дополнительными данными
     */
    public function hasExtendedProfile(): bool
    {
        return $this->consent_pd && (
            $this->last_name || $this->phone || $this->city || $this->church_name
        );
    }

    /**
     * Может ли пользователь загружать/менять аватар?
     * Зависит от согласия на обработку ПДн.
     */
    protected function canUploadAvatar(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->consent_pd === true,
        );
    }
}
