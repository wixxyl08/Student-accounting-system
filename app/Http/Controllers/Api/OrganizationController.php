<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    /**
     * Список организаций с поиском, фильтрацией, сортировкой, пагинацией
     */
    public function index(Request $request)
    {
        $query = Organization::query()
            ->with(['creator:id,full_name'])
            ->withCount('employees');

        // Поиск по ключевым полям
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%")
                  ->orWhere('inn', 'like', "%{$search}%")
                  ->orWhere('ogrn', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        // Фильтр: только с email / без email
        if ($request->has('has_email')) {
            $request->boolean('has_email')
                ? $query->whereNotNull('email')
                : $query->whereNull('email');
        }

        // Сортировка
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSort = ['full_name', 'short_name', 'inn', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSort)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        // Пагинация
        $perPage = min((int) $request->input('per_page', 20), 100);
        $organizations = $query->paginate($perPage);

        return OrganizationResource::collection($organizations);
    }

    /**
     * Создание организации
     */
    public function store(StoreOrganizationRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $organization = Organization::create($data);

        return (new OrganizationResource($organization))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Просмотр одной организации
     */
    public function show(Organization $organization)
    {
        $organization->load([
            'creator:id,full_name,role',
            'updater:id,full_name,role',
        ])->loadCount('employees');
    
    return new OrganizationResource($organization);
    }

    /**
     * Обновление организации
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization)
    {
        $data = $request->validated();
        $data['updated_by'] = auth()->id();

        $organization->update($data);

        return new OrganizationResource($organization);
    }

    /**
     * Мягкое удаление организации
     */
    public function destroy(Organization $organization)
    {
        // Проверка: есть ли привязанные сотрудники
        if ($organization->employees()->exists()) {
            return response()->json([
                'message' => 'Нельзя удалить организацию, к которой привязаны сотрудники.',
            ], 422);
        }

        $organization->delete();  // soft delete

        return response()->json([
            'message' => 'Организация успешно удалена.',
        ]);
    }
}