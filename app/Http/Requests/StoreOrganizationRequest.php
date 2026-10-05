<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'inn' => 'nullable|string|size:10|unique:organizations,inn',
            'kpp' => 'nullable|string|size:9',
            'ogrn' => 'nullable|string|size:13|unique:organizations,ogrn',
            'legal_address' => 'nullable|string|max:500',
            'actual_address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_position' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Полное наименование организации обязательно.',
            'inn.size' => 'ИНН должен содержать ровно 10 цифр.',
            'inn.unique' => 'Организация с таким ИНН уже существует.',
            'kpp.size' => 'КПП должен содержать ровно 9 цифр.',
            'ogrn.size' => 'ОГРН должен содержать ровно 13 цифр.',
            'ogrn.unique' => 'Организация с таким ОГРН уже существует.',
            'email.email' => 'Введите корректный email.',
        ];
    }
}