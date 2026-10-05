<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'program_id' => $this->program_id,
            'program' => $this->whenLoaded('program', fn() => [
                'id' => $this->program->id,
                'name' => $this->program->name,
                'price' => (float) $this->program->price,
                'retraining_period' => $this->program->retraining_period,
                'education_requirement' => $this->program->education_requirement,
            ]),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_name' => $this->status_name,
            'note' => $this->note,
            'enrollments_count' => $this->whenCounted('enrollments'),
            'employees_count' => $this->whenCounted('employees'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'creator' => $this->whenLoaded('creator', fn() => [
    'id' => $this->creator->id,
    'full_name' => $this->creator->full_name,
    'role' => $this->creator->role,
]),

'updater' => $this->whenLoaded('updater', fn() => [
    'id' => $this->updater->id,
    'full_name' => $this->updater->full_name,
    'role' => $this->updater->role,
]),
        ];
    }
}
