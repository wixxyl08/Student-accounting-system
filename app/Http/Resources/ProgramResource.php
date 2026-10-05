<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'education_requirement' => $this->education_requirement,
            'education_requirement_name' => $this->getEducationRequirementName(),
            'retraining_period' => $this->retraining_period,
            'retraining_period_name' => $this->retraining_period_name,
            'custom_months' => $this->custom_months,
            'retraining_months' => $this->retraining_months,
            'duration' => $this->duration,
            'status' => $this->status,
            'notify_days_before' => $this->notify_days_before,
            'groups_count' => $this->whenCounted('groups'),
            'enrollments_count' => $this->whenCounted('enrollments'),
            'contracts_count' => $this->whenCounted('contracts'),
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

    /**
     * Человекочитаемое название требований к образованию
     */
    private function getEducationRequirementName(): string
    {
        return match ($this->education_requirement) {
            'none' => 'Не требуется',
            'secondary_professional' => 'Средне-профессиональное',
            'higher' => 'Высшее',
            default => 'Не указано',
        };
    }
}
