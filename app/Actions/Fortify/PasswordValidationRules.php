<?php

namespace App\Actions\Fortify;

use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, mixed>
     */
    protected function passwordRules(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            Password::min(8)
                ->letters()      // только латиница (без кириллицы)
                ->mixedCase()    // хотя бы одна заглавная и одна строчная
                ->numbers()      // хотя бы одна цифра
                ->symbols(),     // хотя бы один спецсимвол (!@#$%...)
        ];
    }

    protected function currentPasswordRules(): array
    {
        return ['required', 'string', 'current_password'];
    }
}
