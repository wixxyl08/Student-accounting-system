<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'education_requirement' => 'nullable|in:none,secondary_professional,higher',
            'retraining_period' => 'required|in:none,1_year,3_years,5_years,custom',
            'custom_months' => 'nullable|required_if:retraining_period,custom|integer|min:1|max:120',
            'duration' => 'nullable|string|max:255',
            'status' => 'required|in:active,archive',
            'notify_days_before' => 'nullable|integer|min:1|max:365',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название программы обязательно.',
            'price.required' => 'Стоимость обязательна.',
            'price.numeric' => 'Стоимость должна быть числом.',
            'price.min' => 'Стоимость не может быть отрицательной.',
            'retraining_period.required' => 'Периодичность повторного обучения обязательна.',
            'retraining_period.in' => 'Неверное значение периодичности.',
            'custom_months.required_if' => 'Укажите количество месяцев для произвольного срока.',
            'custom_months.min' => 'Количество месяцев должно быть больше 0.',
            'custom_months.max' => 'Количество месяцев не может превышать 120 (10 лет).',
            'status.required' => 'Статус обязателен.',
            'status.in' => 'Статус должен быть: active или archive.',
            'notify_days_before.min' => 'Заблаговременность должна быть не менее 1 дня.',
            'notify_days_before.max' => 'Заблаговременность не может превышать 365 дней.',
        ];
    }
}