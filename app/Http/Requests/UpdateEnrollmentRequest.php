<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:enrolled,studying,completed,expelled',
            'certificate_number' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Статус обучения обязателен.',
            'status.in' => 'Неверный статус обучения.',
        ];
    }
}
