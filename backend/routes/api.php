<?php

use App\Http\Controllers\Api\AttendanceReportController;
use App\Http\Controllers\Api\DashboardAnalyticsController;
use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WorkPulse HR API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // 1. Employee Search & Dropdown
    Route::get('/employees', [EmployeeController::class, 'index']);

    // 2. Attendance Reports
    Route::prefix('reports')->group(function () {
        // Report 1: In-Out Log Report (with column filters & search)
        Route::get('/in-out', [AttendanceReportController::class, 'inOutReport']);
        
        // Report 2: Working Hours Summary Report (with column filters & search)
        Route::get('/working-hours', [AttendanceReportController::class, 'workingHoursReport']);
    });

    // 3. Dashboard Analytics & Dynamic Bar Chart (30-second live polling)
    Route::get('/dashboard/analytics', [DashboardAnalyticsController::class, 'analytics'])
        ->middleware('throttle:120,1');
});

// Backward-compatible unversioned aliases
Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/reports/in-out', [AttendanceReportController::class, 'inOutReport']);
Route::get('/reports/working-hours', [AttendanceReportController::class, 'workingHoursReport']);
Route::get('/dashboard/analytics', [DashboardAnalyticsController::class, 'analytics'])
    ->middleware('throttle:120,1');
