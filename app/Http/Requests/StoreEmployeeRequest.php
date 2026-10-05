<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'organization_id' => 'nullable|integer|exists:organizations,id',
            'position' => 'nullable|string|max:255',
            'status' => 'required|in:active,fired',
        ];
    }

    public function messages(): array
    {
        return [
            'last_name.required' => 'Фамилия обязательна.',
            'first_name.required' => 'Имя обязательно.',
            'birth_date.before' => 'Дата рождения должна быть в прошлом.',
            'email.email' => 'Введите корректный email.',
            'organization_id.exists' => 'Выбранная организация не найдена.',
            'status.required' => 'Статус обязателен.',
            'status.in' => 'Статус должен быть: active или fired.',
        ];
    }
}
