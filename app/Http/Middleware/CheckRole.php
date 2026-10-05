<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Проверяет, что у пользователя нужная роль.
     * Использование в маршрутах: ->middleware('role:admin')
     * Можно указать несколько ролей: ->middleware('role:admin,methodist')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Не авторизован.',
            ], 401);
        }

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Доступ запрещён. Недостаточно прав.',
            ], 403);
        }

        return $next($request);
    }
}
