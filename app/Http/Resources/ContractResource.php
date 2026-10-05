<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'group_id' => $this->group_id,
            'group' => $this->whenLoaded('group', fn() => [
                'id' => $this->group->id,
                'name' => $this->group->name,
            ]),
            'organization_id' => $this->organization_id,
            'organization' => $this->whenLoaded('organization', fn() => [
                'id' => $this->organization->id,
                'full_name' => $this->organization->full_name,
                'short_name' => $this->organization->short_name,
                'inn' => $this->organization->inn,
                'kpp' => $this->organization->kpp,
                'ogrn' => $this->organization->ogrn,
                'legal_address' => $this->organization->legal_address,
            ]),
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', fn() => [
                'id' => $this->employee->id,
                'full_name' => $this->employee->full_name,
            ]),
            'program_id' => $this->program_id,
            'program' => $this->whenLoaded('program', fn() => [
                'id' => $this->program->id,
                'name' => $this->program->name,
                'price' => (float) $this->program->price,
            ]),
            'customer_name' => $this->customer_name,
            'total_amount' => (float) $this->total_amount,
            'file_docx_url' => $this->file_docx_path
                ? url("/api/contracts/{$this->id}/download-docx")
                : null,
            'file_pdf_url' => $this->file_pdf_path
                ? url("/api/contracts/{$this->id}/download-pdf")
                : null,
            'has_docx' => !is_null($this->file_docx_path),
            'has_pdf' => !is_null($this->file_pdf_path),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
