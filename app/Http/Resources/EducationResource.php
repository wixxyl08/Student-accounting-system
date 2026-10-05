<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'level' => $this->level,
            'level_name' => $this->level_name,           // аксессор из модели
            'institution' => $this->institution,
            'graduation_year' => $this->graduation_year,
            'specialty' => $this->specialty,
            'file_path' => $this->file_path,
            'file_url' => $this->file_path 
                ? url("/api/educations/{$this->id}/download") 
                : null,
            'has_file' => !is_null($this->file_path),
            'employee' => $this->whenLoaded('employee', fn() => [
                'id' => $this->employee->id,
                'full_name' => $this->employee->full_name,
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
