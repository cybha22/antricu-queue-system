<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CounterController;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Operator\QueueCallController;
use App\Http\Controllers\Kiosk\KioskQueueController;

Route::prefix('v1')->group(function () {

    Route::post('/auth/login', [LoginController::class, 'login']);
    Route::post('/auth/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/auth/user', [LoginController::class, 'user'])->middleware('auth:sanctum');

    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::apiResource('users', UserController::class)->except('show');
        Route::apiResource('services', ServiceController::class)->except('show');
        Route::apiResource('counters', CounterController::class)->except('show');
        Route::get('queues', [QueueController::class, 'index']);
        Route::delete('queues/{queue}', [QueueController::class, 'destroy']);
        Route::get('settings', [SettingController::class, 'index']);
        Route::put('settings/{setting}', [SettingController::class, 'update']);
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('reports', [ReportController::class, 'index']);
    });

    Route::prefix('operator')->middleware(['auth:sanctum', 'role:admin,operator'])->group(function () {
        Route::get('queue-call', [QueueCallController::class, 'index']);
        Route::post('queue-call/open', [QueueCallController::class, 'open']);
        Route::post('queue-call/close', [QueueCallController::class, 'close']);
        Route::post('queue-call/next', [QueueCallController::class, 'callNext']);
        Route::post('queue-call/serve', [QueueCallController::class, 'serve']);
        Route::post('queue-call/complete', [QueueCallController::class, 'complete']);
        Route::post('queue-call/skip', [QueueCallController::class, 'skip']);
    });

    Route::prefix('kiosk')->group(function () {
        Route::get('services', [KioskQueueController::class, 'services']);
        Route::post('queue', [KioskQueueController::class, 'store']);
        Route::get('queue/{number}/status', [KioskQueueController::class, 'status']);
        Route::get('display', [KioskQueueController::class, 'display']);
    });

});
