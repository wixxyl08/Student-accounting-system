<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id' => 'required|integer|exists:groups,id',
            'organization_id' => 'nullable|integer|exists:organizations,id',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'program_id' => 'required|integer|exists:programs,id',
        ];
    }

    public function messages(): array
    {
        return [
            'group_id.required' => 'Не выбрана группа обучения.',
            'group_id.exists' => 'Группа не найдена.',
            'program_id.required' => 'Не выбрана программа обучения.',
            'program_id.exists' => 'Программа не найдена.',
            'organization_id.exists' => 'Организация не найдена.',
            'employee_id.exists' => 'Сотрудник не найден.',
        ];
    }
}
