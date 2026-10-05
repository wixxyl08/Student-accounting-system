<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEducationRequest;
use App\Http\Requests\UpdateEducationRequest;
use App\Http\Resources\EducationResource;
use App\Models\ActivityLog;
use App\Models\Education;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EducationController extends Controller
{
    /**
     * Список записей об образовании
     * GET /api/educations?employee_id=1
     */
    public function index(Request $request)
    {
        $query = Education::query()
            ->with(['employee:id,last_name,first_name,middle_name']);

        // Обязательный фильтр по сотруднику (если передан)
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        // Фильтр по уровню
        if ($level = $request->input('level')) {
            $query->where('level', $level);
        }

        // Сортировка
        $sortBy = $request->input('sort_by', 'graduation_year');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSort = ['graduation_year', 'level', 'created_at'];

        if (in_array($sortBy, $allowedSort)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        // Пагинация
        $perPage = min((int) $request->input('per_page', 20), 100);
        $educations = $query->paginate($perPage);

        return EducationResource::collection($educations);
    }

    /**
     * Создание записи об образовании
     * POST /api/educations
     * multipart/form-data (из-за файла)
     */
    public function store(StoreEducationRequest $request)
    {
        // Проверка: сотрудник не уволен
        $employee = Employee::find($request->input('employee_id'));
        if ($employee->status === 'fired') {
            return response()->json([
                'message' => 'Нельзя добавить образование уволенному сотруднику.',
            ], 422);
        }

        $data = $request->validated();
        unset($data['file']);   // убираем файл из массового заполнения

        // Загружаем файл, если есть
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('educations', 'public');
            $data['file_path'] = $path;
        }

        $education = Education::create($data);

        ActivityLog::log('created', $education);

        return (new EducationResource($education->load('employee')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Просмотр одной записи
     * GET /api/educations/{id}
     */
    public function show(Education $education)
    {
        $education->load('employee:id,last_name,first_name,middle_name');

        return new EducationResource($education);
    }

    /**
     * Обновление записи
     * PUT/PATCH /api/educations/{id}
     * multipart/form-data (если обновляется файл)
     */
    public function update(UpdateEducationRequest $request, Education $education)
    {
        $data = $request->validated();
        unset($data['file']);

        // Загружаем новый файл, если есть
        if ($request->hasFile('file')) {
            // Удаляем старый файл
            if ($education->file_path) {
                Storage::disk('public')->delete($education->file_path);
            }
            // Сохраняем новый
            $path = $request->file('file')->store('educations', 'public');
            $data['file_path'] = $path;
        }

        $education->update($data);

        ActivityLog::log('updated', $education);

        return new EducationResource($education->load('employee'));
    }

    /**
     * Удаление записи
     * DELETE /api/educations/{id}
     */
    public function destroy(Education $education)
    {
        // Удаляем файл, если есть
        if ($education->file_path) {
            Storage::disk('public')->delete($education->file_path);
        }

        ActivityLog::log('deleted', $education);
        $education->delete();   // физическое удаление

        return response()->json([
            'message' => 'Запись об образовании удалена.',
        ]);
    }

    /**
     * Скачивание файла диплома
     * GET /api/educations/{id}/download
     */
    public function download(Education $education)
    {
        if (!$education->file_path) {
            return response()->json([
                'message' => 'У этой записи нет прикреплённого файла.',
            ], 404);
        }

        if (!Storage::disk('public')->exists($education->file_path)) {
            return response()->json([
                'message' => 'Файл не найден на сервере.',
            ], 404);
        }

        return Storage::disk('public')->download($education->file_path);
    }
}