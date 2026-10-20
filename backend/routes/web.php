<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware('web')->group(function () {
    Route::get('/', function (Request $request) {
        try {
            DB::connection()->getPdo();
            $dbStatus = 'Connected (TiDB Cloud MySQL)';
        } catch (\Throwable $e) {
            $dbStatus = 'Error: ' . $e->getMessage();
        }

        if ($request->expectsJson() || $request->is('json*')) {
            return response()->json([
                'status' => 'online',
                'service' => 'WorkPulse HR Backend RESTful API',
                'database' => $dbStatus,
                'endpoints' => [
                    'dashboard_analytics' => '/api/dashboard/analytics',
                    'in_out_report' => '/api/reports/in-out',
                    'working_hours_report' => '/api/reports/working-hours',
                    'employees' => '/api/employees',
                ],
                'version' => '1.0.0',
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        return view('welcome', [
            'dbStatus' => $dbStatus,
        ]);
    });
});
