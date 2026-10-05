<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'login' => 'required|string|max:100|unique:users,login',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,methodist',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'ФИО обязательно.',
            'login.required' => 'Логин обязателен.',
            'login.unique' => 'Пользователь с таким логином уже существует.',
            'password.required' => 'Пароль обязателен.',
            'password.min' => 'Пароль должен быть не менее 6 символов.',
            'role.required' => 'Роль обязательна.',
            'role.in' => 'Роль должна быть: admin или methodist.',
        ];
    }
}