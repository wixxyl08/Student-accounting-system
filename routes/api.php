<?php

use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\EducationController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\Admin\ActivityLogController;
/*
Публичные маршруты
*/

Route::post('/login', [AuthController::class, 'login']);

/*
Защищённые маршруты (нужен токен)
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    /*
    Организации — доступны админам и методистам
    */
    Route::middleware('role:admin,methodist')->group(function () {
        Route::apiResource('organizations', OrganizationController::class);
        Route::apiResource('employees', EmployeeController::class);
        Route::apiResource('programs', ProgramController::class);
        Route::apiResource('educations', EducationController::class);
        Route::apiResource('groups', GroupController::class);
        Route::apiResource('enrollments', EnrollmentController::class);
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/stats', [NotificationController::class, 'stats']);
        Route::apiResource('contracts', ContractController::class);
        Route::get('contracts/{contract}/download-docx', [ContractController::class, 'downloadDocx']);
        Route::get('contracts/{contract}/download-pdf', [ContractController::class, 'downloadPdf']);
        Route::prefix('export')->group(function () {
        Route::get('organizations', [ExportController::class, 'organizations']);
        Route::get('employees', [ExportController::class, 'employees']);
        Route::get('groups', [ExportController::class, 'groups']);
        Route::get('notifications', [ExportController::class, 'notifications']);
        });
        Route::get('educations/{education}/download', [EducationController::class, 'download']);
        Route::get('dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'index']);
        });
});

/*
Админские маршруты
*/

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/test', function () {
            return response()->json([
                'message' => 'Только для администраторов',
                'user' => auth()->user()->full_name,
            ]);
        });
        Route::apiResource('users', AdminUserController::class);
        Route::post('users/{user}/toggle-block', [AdminUserController::class, 'toggleBlock']);
                Route::get('activity-logs', [\App\Http\Controllers\Api\Admin\ActivityLogController::class, 'index']);
    });

/*
Общие маршруты (админ + методист)
*/

Route::middleware(['auth:sanctum', 'role:admin,methodist'])
    ->prefix('common')
    ->group(function () {
        Route::get('/test', function () {
            return response()->json([
                'message' => 'Доступно админам и методистам',
                'user' => auth()->user()->full_name,
            ]);
        });
    });