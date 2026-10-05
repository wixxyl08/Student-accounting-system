<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
            'group_id' => 'required|integer|exists:groups,id',
            'status' => 'nullable|in:enrolled,studying,completed,expelled',
            'certificate_number' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Не указан сотрудник.',
            'employee_id.exists' => 'Сотрудник не найден.',
            'group_id.required' => 'Не указана группа.',
            'group_id.exists' => 'Группа не найдена.',
            'status.in' => 'Неверный статус обучения.',
        ];
    }
}
