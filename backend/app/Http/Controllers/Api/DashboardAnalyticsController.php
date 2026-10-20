<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardAnalyticsController extends Controller
{
    public function __construct(
        protected AttendanceReportService $reportService
    ) {}

    /**
     * Dashboard 1: Dynamic Bar Chart Feed (30-second live polling endpoint)
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'from_date' => 'nullable|date_format:Y-m-d',
                'to_date' => 'nullable|date_format:Y-m-d',
            ]);

            $result = $this->reportService->getDashboardAnalytics(
                $validated['from_date'] ?? null,
                $validated['to_date'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
            ], 200);
        }
    }
}
