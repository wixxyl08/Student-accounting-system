<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'full_name' => $this->full_name,          // аксессор из модели
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'phone' => $this->phone,
            'email' => $this->email,
            'organization_id' => $this->organization_id,
            'organization' => $this->whenLoaded('organization', fn() => [
                'id' => $this->organization->id,
                'full_name' => $this->organization->full_name,
                'short_name' => $this->organization->short_name,
            ]),
            'position' => $this->position,
            'status' => $this->status,
            'fired_at' => $this->fired_at?->format('Y-m-d'),
            'educations_count' => $this->whenCounted('educations'),
            'enrollments_count' => $this->whenCounted('enrollments'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'creator' => $this->whenLoaded('creator', fn() => [
            'id' => $this->creator->id,
            'full_name' => $this->creator->full_name,
            'role' => $this->creator->role,
]),

'       updater' => $this->whenLoaded('updater', fn() => [
        'id' => $this->updater->id,
        'full_name' => $this->updater->full_name,
        'role' => $this->updater->role,
]),
        ];
    }
}