<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskAuditController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (using session auth for simplicity)
Route::middleware('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users', [AuthController::class, 'users']);
    
    Route::apiResource('tasks', TaskController::class);
    
    // Audit trail routes
    Route::prefix('tasks/{taskId}/audits')->group(function () {
        Route::get('/', [TaskAuditController::class, 'index'])->name('tasks.audits.index');
        Route::get('/{auditId}', [TaskAuditController::class, 'show'])->name('tasks.audits.show');
    });
});

