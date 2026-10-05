<?php

namespace App\Exports;

use App\Models\Enrollment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NotificationsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        return Enrollment::query()
            ->with(['employee.organization', 'group.program'])
            ->where('status', 'completed')
            ->whereNotNull('next_training_date')
            ->whereHas('employee', fn($q) => $q->where('status', 'active'))
            ->orderBy('next_training_date', 'asc');
    }

    public function headings(): array
    {
        return [
            'Сотрудник', 'Организация', 'Программа',
            'Дата прохождения', 'Дата повторного обучения', 'Дней до повторного',
        ];
    }

    public function map($enrollment): array
    {
        return [
            $enrollment->employee?->full_name ?? '—',
            $enrollment->employee?->organization?->full_name ?? '—',
            $enrollment->group?->program?->name ?? '—',
            $enrollment->completed_at?->format('Y-m-d'),
            $enrollment->next_training_date?->format('Y-m-d'),
            $enrollment->days_until_retraining,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}