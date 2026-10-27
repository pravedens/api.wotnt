<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'max:5120'], // до 5 МБ
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Выберите файл изображения.',
            'avatar.image' => 'Файл должен быть изображением.',
            'avatar.max' => 'Размер изображения не должен превышать 5 МБ.',
        ];
    }
}
