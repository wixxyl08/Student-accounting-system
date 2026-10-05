<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('user')->id;

        return [
            'full_name' => 'required|string|max:255',
            'login' => ['required', 'string', 'max:100', Rule::unique('users', 'login')->ignore($id)],
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,methodist',
            'is_blocked' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'ФИО обязательно.',
            'login.required' => 'Логин обязателен.',
            'login.unique' => 'Пользователь с таким логином уже существует.',
            'password.min' => 'Пароль должен быть не менее 6 символов.',
            'role.required' => 'Роль обязательна.',
        ];
    }
}