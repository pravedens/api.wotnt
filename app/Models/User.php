<?php

namespace App\Models;

// use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Models\Concerns\HasStorageUrls;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'last_name', 'middle_name', 'phone', 'city', 'church_name',
    'birth_date', 'about', 'marital_status', 'gender',
    'avatar_path', 'consent_pd', 'consent_pd_at', 'consent_pd_ip', 'is_deceased', 'death_date', 'funeral_date',
])]
#[Hidden(['password', 'remember_token', 'consent_pd_ip'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, MustVerifyEmail, PasskeyUser
{
    use HasApiTokens, HasFactory, HasRoles, HasStorageUrls, InteractsWithAppAuthentication, InteractsWithAppAuthenticationRecovery, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

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
            'is_deceased' => 'boolean',
            'death_date' => 'date',
            'funeral_date' => 'date',
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
        ];
    }

    /**
     * Полный URL аватара (S3)
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->storageUrl($this->avatar_path),
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

    public function canAccessPanel(Panel $panel): bool
    {
        // Разрешаем доступ только пользователям с ролью super_admin
        return $this->hasRole(['super_admin']);
    }

    public function getAppAuthenticationSecret(): ?string
    {
        // Возвращаем значение из поля, которое вы создадите в БД
        return $this->app_authentication_secret;
    }

    public function saveAppAuthenticationSecret(?string $secret): void
    {
        // Сохраняем секрет в поле
        $this->app_authentication_secret = $secret;
        $this->save();
    }

    public function getAppAuthenticationHolderName(): string
    {
        // Имя, которое пользователь увидит в приложении (например, email)
        return $this->email;
    }

    // Чтение кодов
    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->app_authentication_recovery_codes;
    }

    // Сохранение кодов
    public function saveAppAuthenticationRecoveryCodes(?array $codes): void
    {
        $this->app_authentication_recovery_codes = $codes;
        $this->save();
    }
}
