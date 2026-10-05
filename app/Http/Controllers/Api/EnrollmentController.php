<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Requests\UpdateEnrollmentRequest;
use App\Http\Resources\EnrollmentResource;
use App\Models\ActivityLog;
use App\Models\Education;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\Group;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Список зачислений с фильтрами
     * GET /api/enrollments
     */
    public function index(Request $request)
    {
        $query = Enrollment::query()
            ->with([
                'employee:id,last_name,first_name,middle_name,status',
                'group:id,name,status,program_id',
            ]);

        // Фильтр по сотруднику
        if ($employeeId = $request->input('employee_id')) {
            $query->where('employee_id', $employeeId);
        }

        // Фильтр по группе
        if ($groupId = $request->input('group_id')) {
            $query->where('group_id', $groupId);
        }

        // Фильтр по статусу
        if ($status = $request->input('status')) {
            if (in_array($status, ['enrolled', 'studying', 'completed', 'expelled'])) {
                $query->where('status', $status);
            }
        }

        // Сортировка
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSort = ['created_at', 'updated_at', 'status', 'completed_at', 'next_training_date'];

        if (in_array($sortBy, $allowedSort)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        // Пагинация
        $perPage = min((int) $request->input('per_page', 20), 100);
        $enrollments = $query->paginate($perPage);

        return EnrollmentResource::collection($enrollments);
    }

    /**
     * Создание зачисления (добавление сотрудника в группу)
     * POST /api/enrollments
     */
    public function store(StoreEnrollmentRequest $request)
    {
        $data = $request->validated();

        // 1. Проверка: сотрудник не уволен
        $employee = Employee::find($data['employee_id']);
        if ($employee->status === 'fired') {
            return response()->json([
                'message' => 'Нельзя зачислить уволенного сотрудника.',
            ], 422);
        }

        // 2. Проверка: нет дублирования в этой группе
        $exists = Enrollment::where('employee_id', $data['employee_id'])
            ->where('group_id', $data['group_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Этот сотрудник уже зачислен в данную группу.',
            ], 422);
        }

        // 3. Проверка: соответствует ли уровень образования требованиям программы
        $group = Group::with('program')->find($data['group_id']);
        $requirement = $group->program->education_requirement;

        if ($requirement !== 'none') {
            $hasEducation = Education::where('employee_id', $data['employee_id'])
                ->where('level', $requirement)
                ->exists();

            if (!$hasEducation) {
                return response()->json([
                    'message' => 'У сотрудника нет образования нужного уровня (' . $requirement . ').',
                ], 422);
            }
        }

        // По умолчанию статус — enrolled
        $data['status'] = $data['status'] ?? 'enrolled';

        $enrollment = Enrollment::create($data);

        ActivityLog::log('created', $enrollment);

        return (new EnrollmentResource($enrollment->load(['employee', 'group'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Просмотр одного зачисления
     * GET /api/enrollments/{id}
     */
    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['employee', 'group.program']);

        return new EnrollmentResource($enrollment);
    }

    /**
     * Обновление зачисления
     * PUT/PATCH /api/enrollments/{id}
     * При смене статуса на "completed" — автоматически рассчитывается дата следующего обучения
     */
    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment)
    {
        $data = $request->validated();
        $oldStatus = $enrollment->status;

        // Если статус меняется на "completed" — рассчитываем дату следующего обучения
        if ($data['status'] === 'completed' && $oldStatus !== 'completed') {
            $data['completed_at'] = now();

            // Загружаем программу через группу
            $program = $enrollment->group->program;
            if ($program) {
                $nextDate = $program->calculateNextTrainingDate(now());
                $data['next_training_date'] = $nextDate;
            }
        }

        // Если статус возвращают из "completed" — очищаем даты
        if ($data['status'] !== 'completed' && $oldStatus === 'completed') {
            $data['completed_at'] = null;
            $data['next_training_date'] = null;
        }

        $enrollment->update($data);

        ActivityLog::log('updated', $enrollment);

        return new EnrollmentResource($enrollment->load(['employee', 'group']));
    }

    /**
     * Удаление зачисления (отчисление)
     * DELETE /api/enrollments/{id}
     */
    public function destroy(Enrollment $enrollment)
    {
        ActivityLog::log('deleted', $enrollment);
        $enrollment->delete();

        return response()->json([
            'message' => 'Зачисление удалено.',
        ]);
    }
}