<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Вход в систему
     * POST /api/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('login', $request->login)->first();

        // Проверка: пользователь найден и пароль верный
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Неверный логин или пароль.'],
            ]);
        }

        // Проверка: пользователь не заблокирован
        if ($user->is_blocked) {
            throw ValidationException::withMessages([
                'login' => ['Учётная запись заблокирована.'],
            ]);
        }

        // Обновляем время последнего входа
        $user->last_login_at = now();
        $user->save();

        // Удаляем старые токены и создаём новый
        // $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'login' => $user->login,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Выход из системы
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Вы вышли из системы.']);
    }

    /**
     * Текущий пользователь
     * GET /api/me
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'full_name' => $user->full_name,
            'login' => $user->login,
            'role' => $user->role,
            'last_login_at' => $user->last_login_at,
        ]);
    }
}
