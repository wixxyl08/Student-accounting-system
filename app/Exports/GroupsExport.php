<?php

namespace App\Exports;

use App\Models\Group;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GroupsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Group::query()->with('program')->withCount('enrollments');

        if (!empty($this->filters['search'])) {
            $query->where('name', 'like', "%{$this->filters['search']}%");
        }

        if (!empty($this->filters['program_id'])) {
            $query->where('program_id', $this->filters['program_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('start_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID', 'Название группы', 'Программа',
            'Дата начала', 'Дата окончания',
            'Статус', 'Обучающихся', 'Дата создания',
        ];
    }

    public function map($group): array
    {
        $statuses = [
            'recruiting' => 'Набор',
            'ongoing' => 'Идёт обучение',
            'finished' => 'Завершена',
            'cancelled' => 'Отменена',
        ];

        return [
            $group->id, $group->name,
            $group->program?->name ?? '—',
            $group->start_date?->format('Y-m-d'),
            $group->end_date?->format('Y-m-d'),
            $statuses[$group->status] ?? $group->status,
            $group->enrollments_count,
            $group->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}