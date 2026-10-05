<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Список пользователей
     * GET /api/admin/users
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('login', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $users = $query->orderBy('id')->paginate($perPage);

        return UserResource::collection($users);
    }

    /**
     * Создание пользователя
     * POST /api/admin/users
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['is_blocked'] = false;

        $user = User::create($data);

        ActivityLog::log('created', $user);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Просмотр пользователя
     * GET /api/admin/users/{id}
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Обновление пользователя
     * PUT/PATCH /api/admin/users/{id}
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        ActivityLog::log('updated', $user);

        return new UserResource($user);
    }

    /**
     * Блокировка/разблокировка пользователя
     * POST /api/admin/users/{id}/toggle-block
     */
    public function toggleBlock(User $user)
    {
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        ActivityLog::log('updated', $user);

        return response()->json([
            'message' => $user->is_blocked ? 'Пользователь заблокирован.' : 'Пользователь разблокирован.',
            'is_blocked' => $user->is_blocked,
        ]);
    }

    /**
     * Удаление пользователя (soft delete)
     * DELETE /api/admin/users/{id}
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'Нельзя удалить самого себя.',
            ], 422);
        }

        ActivityLog::log('deleted', $user);
        $user->delete();

        return response()->json([
            'message' => 'Пользователь удалён.',
        ]);
    }
}