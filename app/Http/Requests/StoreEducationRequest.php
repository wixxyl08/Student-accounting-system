<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
            'level' => 'required|in:higher,secondary_professional,secondary_general,other',
            'institution' => 'nullable|string|max:255',
            'graduation_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'specialty' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10 МБ
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Не указан сотрудник.',
            'employee_id.exists' => 'Сотрудник не найден.',
            'level.required' => 'Уровень образования обязателен.',
            'level.in' => 'Неверный уровень образования.',
            'graduation_year.min' => 'Год окончания не может быть раньше 1900.',
            'graduation_year.max' => 'Год окончания не может быть в будущем.',
            'file.mimes' => 'Файл должен быть в формате PDF, JPG или PNG.',
            'file.max' => 'Размер файла не должен превышать 10 МБ.',
        ];
    }
}
