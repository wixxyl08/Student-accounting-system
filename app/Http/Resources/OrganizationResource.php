<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'short_name' => $this->short_name,
            'inn' => $this->inn,
            'kpp' => $this->kpp,
            'ogrn' => $this->ogrn,
            'legal_address' => $this->legal_address,
            'actual_address' => $this->actual_address,
            'phone' => $this->phone,
            'email' => $this->email,
            'contact_person' => $this->contact_person,
            'contact_position' => $this->contact_position,
            'note' => $this->note,
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