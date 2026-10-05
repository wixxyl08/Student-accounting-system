<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', fn() => [
                'id' => $this->employee->id,
                'full_name' => $this->employee->full_name,
                'status' => $this->employee->status,
            ]),
            'group_id' => $this->group_id,
            'group' => $this->whenLoaded('group', fn() => [
                'id' => $this->group->id,
                'name' => $this->group->name,
                'status' => $this->group->status,
            ]),
            'status' => $this->status,
            'status_name' => $this->status_name,
            'completed_at' => $this->completed_at?->format('Y-m-d'),
            'next_training_date' => $this->next_training_date?->format('Y-m-d'),
            'days_until_retraining' => $this->days_until_retraining,
            'urgency_color' => $this->urgency_color,
            'certificate_number' => $this->certificate_number,
            'note' => $this->note,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}