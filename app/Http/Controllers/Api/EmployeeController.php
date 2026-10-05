<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Список сотрудников с поиском, фильтрами, пагинацией
     */
    public function index(Request $request)
    {
        $query = Employee::query()
            ->with(['organization:id,full_name,short_name'])
            ->withCount(['educations', 'enrollments']);

        // Поиск по ФИО и контактам
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Фильтр по организации
        if ($request->has('organization_id')) {
            $request->input('organization_id') === 'null'
                ? $query->whereNull('organization_id')    // только физлица
                : $query->where('organization_id', $request->input('organization_id'));
        }

        // Фильтр по статусу
        if ($status = $request->input('status')) {
            if (in_array($status, ['active', 'fired'])) {
                $query->where('status', $status);
            }
        }

        // Сортировка
        $sortBy = $request->input('sort_by', 'last_name');
        $sortDir = $request->input('sort_dir', 'asc');
        $allowedSort = ['last_name', 'first_name', 'created_at', 'updated_at', 'status'];

        if (in_array($sortBy, $allowedSort)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        // Пагинация
        $perPage = min((int) $request->input('per_page', 20), 100);
        $employees = $query->paginate($perPage);

        return EmployeeResource::collection($employees);
    }

    /**
     * Создание сотрудника
     */
    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        // Автоматически ставим дату увольнения, если статус fired
        if ($data['status'] === 'fired') {
            $data['fired_at'] = now();
        }

        $employee = Employee::create($data);

        return (new EmployeeResource($employee))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Просмотр одного сотрудника
     */
    public function show(Employee $employee)
    {
        $employee->load([
            'organization:id,full_name,short_name',
            'creator:id,full_name,role',
            'updater:id,full_name,role',
        ])->loadCount(['educations', 'enrollments']);
    
        return new EmployeeResource($employee);
    }

    /**
     * Обновление сотрудника
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        // Логика статуса:
        // - active → fired: ставим дату увольнения
        // - fired → active: очищаем дату увольнения
        if ($data['status'] === 'fired' && $employee->status !== 'fired') {
            $data['fired_at'] = now();
        } elseif ($data['status'] === 'active' && $employee->status === 'fired') {
            $data['fired_at'] = null;
        }

        $employee->update($data);


        return new EmployeeResource($employee);
    }

    /**
     * Мягкое удаление сотрудника
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();  // soft delete

        return response()->json([
            'message' => 'Сотрудник успешно удалён.',
        ]);
    }
}
