<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'level' => 'required|in:higher,secondary_professional,secondary_general,other',
            'institution' => 'nullable|string|max:255',
            'graduation_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'specialty' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'level.required' => 'Уровень образования обязателен.',
            'level.in' => 'Неверный уровень образования.',
            'file.mimes' => 'Файл должен быть в формате PDF, JPG или PNG.',
            'file.max' => 'Размер файла не должен превышать 10 МБ.',
        ];
    }
}
