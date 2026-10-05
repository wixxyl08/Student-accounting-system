<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Models\Program;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Список групп с поиском, фильтрами, пагинацией
     * GET /api/groups
     */
    public function index(Request $request)
    {
        $query = Group::query()
            ->with(['program:id,name,price,retraining_period,education_requirement'])
            ->withCount(['enrollments', 'employees']);

        // Поиск по названию
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Фильтр по программе
        if ($programId = $request->input('program_id')) {
            $query->where('program_id', $programId);
        }

        // Фильтр по статусу
        if ($status = $request->input('status')) {
            if (in_array($status, ['recruiting', 'ongoing', 'finished', 'cancelled'])) {
                $query->where('status', $status);
            }
        }

        // Только активные (recruiting + ongoing)
        if ($request->boolean('only_active')) {
            $query->whereIn('status', ['recruiting', 'ongoing']);
        }

        // Сортировка
        $sortBy = $request->input('sort_by', 'start_date');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSort = ['name', 'start_date', 'end_date', 'created_at', 'status'];

        if (in_array($sortBy, $allowedSort)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        // Пагинация
        $perPage = min((int) $request->input('per_page', 20), 100);
        $groups = $query->paginate($perPage);

        return GroupResource::collection($groups);
    }

    /**
     * Создание группы
     * POST /api/groups
     */
    public function store(StoreGroupRequest $request)
    {
        $data = $request->validated();

        // Если название не указано — генерируем автоматически
        if (empty($data['name'])) {
            $data['name'] = Group::generateName(
                $data['program_id'],
                date('Y', strtotime($data['start_date']))
            );
        }

        $group = Group::create($data);

        return (new GroupResource($group->load('program')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Просмотр одной группы
     * GET /api/groups/{id}
     */
    public function show(Group $group)
    {
        $group->load([
            'program',
            'creator:id,full_name,role',
            'updater:id,full_name,role',
        ])->loadCount(['enrollments', 'employees']);
    
        return new GroupResource($group);
    }

    /**
     * Обновление группы
     * PUT/PATCH /api/groups/{id}
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $data = $request->validated();

        // Если название пустое — оставляем прежнее
        if (empty($data['name'])) {
            unset($data['name']);
        }

        $group->update($data);

        return new GroupResource($group->load('program'));
    }

    /**
     * Удаление группы
     * DELETE /api/groups/{id}
     */
    public function destroy(Group $group)
    {
        // Проверка: есть ли зачисленные ученики
        if ($group->enrollments()->exists()) {
            return response()->json([
                'message' => 'Нельзя удалить группу, в которой есть зачисленные обучающиеся.',
            ], 422);
        }

        $group->delete();

        return response()->json([
            'message' => 'Группа успешно удалена.',
        ]);
    }
}