<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExtendedProfileRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения этого запроса.
     */
    public function authorize(): bool
    {
        // Маршрут уже защищён middleware 'auth:sanctum' и 'verified'
        return true;
    }

    /**
     * Правила валидации.
     */
    public function rules(): array
    {
        return [
            'consent_pd' => ['required', 'boolean'],
            'last_name' => ['nullable', 'string', 'max:100', 'regex:/^[А-Яа-яЁёA-Za-z\s\-]+$/u'],
            'middle_name' => ['nullable', 'string', 'max:100', 'regex:/^[А-Яа-яЁёA-Za-z\s\-]+$/u'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-\(\)]+$/'],
            'city' => ['nullable', 'string', 'max:100'],
            'church_name' => ['nullable', 'string', 'max:150'],
            'birth_date' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'about' => ['nullable', 'string', 'max:1000'],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
        ];
    }

    /**
     * Кастомные сообщения об ошибках.
     */
    public function messages(): array
    {
        return [
            'last_name.regex' => 'Фамилия может содержать только буквы, пробел и дефис.',
            'middle_name.regex' => 'Отчество может содержать только буквы, пробел и дефис.',
            'phone.regex' => 'Введите корректный номер телефона.',
            'birth_date.before' => 'Дата рождения не может быть в будущем.',
            'about.max' => 'Текст «О себе» не должен превышать 1000 символов.',
            'consent_pd.required' => 'Необходимо указать согласие на обработку персональных данных.',
            'consent_pd.boolean' => 'Некорректное значение согласия.',
        ];
    }
}
