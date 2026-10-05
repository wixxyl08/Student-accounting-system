<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProgramRequest;
use App\Http\Requests\UpdateProgramRequest;
use App\Http\Resources\ProgramResource;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Список программ с поиском, фильтрами, пагинацией
     */
    public function index(Request $request)
    {
        $query = Program::query()
            ->withCount(['groups', 'enrollments', 'contracts']);

        // Поиск по названию и описанию
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Фильтр по статусу
        if ($status = $request->input('status')) {
            if (in_array($status, ['active', 'archive'])) {
                $query->where('status', $status);
            }
        }

        // Фильтр: только активные (по умолчанию)
        if ($request->boolean('only_active')) {
            $query->where('status', 'active');
        }

        // Фильтр по требованиям к образованию
        if ($req = $request->input('education_requirement')) {
            $query->where('education_requirement', $req);
        }

        // Сортировка
        $sortBy = $request->input('sort_by', 'name');
        $sortDir = $request->input('sort_dir', 'asc');
        $allowedSort = ['name', 'price', 'created_at', 'updated_at', 'status'];

        if (in_array($sortBy, $allowedSort)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        // Пагинация
        $perPage = min((int) $request->input('per_page', 20), 100);
        $programs = $query->paginate($perPage);

        return ProgramResource::collection($programs);
    }

    /**
     * Создание программы
     */
    public function store(StoreProgramRequest $request)
    {
        $data = $request->validated();
        $data['notify_days_before'] = $data['notify_days_before'] ?? 60;

        $program = Program::create($data);

        return (new ProgramResource($program))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Просмотр одной программы
     */
    public function show(Program $program)
    {
        $program->load([
            'creator:id,full_name,role',
            'updater:id,full_name,role',
        ])->loadCount(['groups', 'enrollments', 'contracts']);
    
        return new ProgramResource($program);
    }

    /**
     * Обновление программы
     */
    public function update(UpdateProgramRequest $request, Program $program)
    {
        $data = $request->validated();

        // Если периодичность не custom — очищаем custom_months
        if ($data['retraining_period'] !== 'custom') {
            $data['custom_months'] = null;
        }

        $program->update($data);

        return new ProgramResource($program);
    }

    /**
     * Архивация программы (soft delete через статус)
     */
    public function destroy(Program $program)
    {
        // Проверка: есть ли активные группы
        if ($program->groups()->whereIn('status', ['recruiting', 'ongoing'])->exists()) {
            return response()->json([
                'message' => 'Нельзя архивировать программу с активными группами.',
            ], 422);
        }

        // Меняем статус на архив (не удаляем физически)
        $program->update(['status' => 'archive']);

        return response()->json([
            'message' => 'Программа перемещена в архив.',
        ]);
    }
}
