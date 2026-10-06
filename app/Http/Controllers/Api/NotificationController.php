<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Список уведомлений о повторном обучении
     * GET /api/notifications
     */
    public function index(Request $request)
    {
        $query = Enrollment::query()
            ->with([
                'employee:id,last_name,first_name,middle_name,status,organization_id',
                'employee.organization:id,full_name,short_name',
                'group:id,name,program_id',
                'group.program:id,name,notify_days_before,retraining_period',
            ])
            ->where('status', 'completed')
            ->whereNotNull('next_training_date')
            ->whereHas('employee', function ($q) {
                $q->where('status', 'active');
            });

        // Фильтр: только те, кому пора (в пределах notify_days_before)
        if (!$request->boolean('show_all')) {
            $query->whereRaw('next_training_date <= DATE_ADD(CURDATE(), INTERVAL COALESCE((SELECT notify_days_before FROM programs WHERE programs.id = (SELECT program_id FROM `groups` WHERE `groups`.id = enrollments.group_id)), 60) DAY)');
        }

        // Поиск по ФИО или программе
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($sub) use ($search) {
                    $sub->where('last_name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%");
                })
                ->orWhereHas('group.program', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Сортировка
        $sortBy = $request->input('sort_by', 'next_training_date');
        $sortDir = $request->input('sort_dir', 'asc');
        $allowedSort = ['next_training_date', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSort)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        // Пагинация
        $perPage = min((int) $request->input('per_page', 50), 200);
        $notifications = $query->paginate($perPage);

        // Обогащаем данными: организация, программа, дни до повторного обучения
        $notifications->getCollection()->transform(function ($enrollment) {
            return [
                'id' => $enrollment->id,
                'employee' => [
                    'id' => $enrollment->employee->id,
                    'full_name' => $enrollment->employee->full_name,
                    'organization' => $enrollment->employee->organization ? [
                        'id' => $enrollment->employee->organization->id,
                        'full_name' => $enrollment->employee->organization->full_name,
                        'short_name' => $enrollment->employee->organization->short_name,
                    ] : null,
                ],
                'program' => [
                    'id' => $enrollment->group->program->id,
                    'name' => $enrollment->group->program->name,
                    'notify_days_before' => $enrollment->group->program->notify_days_before,
                ],
                'group' => [
                    'id' => $enrollment->group->id,
                    'name' => $enrollment->group->name,
                ],
                'completed_at' => $enrollment->completed_at?->format('Y-m-d'),
                'next_training_date' => $enrollment->next_training_date?->format('Y-m-d'),
                'days_until_retraining' => $enrollment->days_until_retraining,
                'urgency_color' => $enrollment->urgency_color,
            ];
        });

        return response()->json($notifications);
    }

    /**
     * Краткая статистика уведомлений
     * GET /api/notifications/stats
     */
    public function stats()
{
    $base = Enrollment::query()
        ->where('status', 'completed')
        ->whereNotNull('next_training_date')
        ->whereHas('employee', fn($q) => $q->where('status', 'active'));

    $all = $base->count();

    $red = (clone $base)
        ->where('next_training_date', '<', now()->addDays(60))
        ->count();

    $yellow = (clone $base)
        ->whereBetween('next_training_date', [now()->addDays(60), now()->addDays(120)])
        ->count();

    $green = (clone $base)
        ->where('next_training_date', '>', now()->addDays(120))
        ->count();

    return response()->json([
        'total'    => $all,
        'red'      => $red,      // менее 60 — включая просроченные
        'yellow'   => $yellow,   // 60–120
        'green'    => $green,    // более 120
    ]);
    }
}
