<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'program_id' => 'required|integer|exists:programs,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:recruiting,ongoing,finished,cancelled',
            'note' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'program_id.required' => 'Необходимо выбрать программу обучения.',
            'start_date.required' => 'Дата начала обучения обязательна.',
            'end_date.after_or_equal' => 'Дата окончания не может быть раньше даты начала.',
            'status.required' => 'Статус группы обязателен.',
        ];
    }
}
