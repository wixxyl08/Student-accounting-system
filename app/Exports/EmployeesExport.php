<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Employee::query()->with('organization');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['organization_id'])) {
            $query->where('organization_id', $this->filters['organization_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('last_name');
    }

    public function headings(): array
    {
        return [
            'ID', 'Фамилия', 'Имя', 'Отчество', 'Дата рождения',
            'Телефон', 'Email', 'Организация', 'Должность',
            'Статус', 'Дата увольнения', 'Дата создания',
        ];
    }

    public function map($emp): array
    {
        return [
            $emp->id, $emp->last_name, $emp->first_name, $emp->middle_name,
            $emp->birth_date?->format('Y-m-d'),
            $emp->phone, $emp->email,
            $emp->organization?->full_name ?? '—',
            $emp->position,
            $emp->status === 'active' ? 'Активен' : 'Уволен',
            $emp->fired_at?->format('Y-m-d'),
            $emp->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}