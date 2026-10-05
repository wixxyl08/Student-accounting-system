<?php

namespace App\Exports;

use App\Models\Organization;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrganizationsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Organization::query();

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%")
                  ->orWhere('inn', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('full_name');
    }

    public function headings(): array
    {
        return [
            'ID', 'Полное наименование', 'Краткое наименование',
            'ИНН', 'КПП', 'ОГРН',
            'Юридический адрес', 'Фактический адрес',
            'Телефон', 'Email', 'Контактное лицо', 'Должность',
            'Дата создания',
        ];
    }

    public function map($org): array
    {
        return [
            $org->id, $org->full_name, $org->short_name,
            $org->inn, $org->kpp, $org->ogrn,
            $org->legal_address, $org->actual_address,
            $org->phone, $org->email, $org->contact_person, $org->contact_position,
            $org->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
