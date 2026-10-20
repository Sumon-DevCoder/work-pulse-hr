<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceReportController extends Controller
{
    public function __construct(
        protected AttendanceReportService $reportService
    ) {}

    /**
     * Report 1: Employee and Date-wise Attendance In-Out Report
     */
    public function inOutReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'emp_id' => 'nullable|string',
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date' => 'nullable|date_format:Y-m-d',
            'search' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|string|in:emp_id,emp_name,date_time,in_out',
            'sort_dir' => 'nullable|string|in:asc,desc',
            'filters' => 'nullable|array',
            'filters.emp_id' => 'nullable|string|max:50',
            'filters.emp_name' => 'nullable|string|max:100',
            'filters.date_time' => 'nullable|string|max:50',
            'filters.in_out' => 'nullable|string|max:10',
        ]);

        $paginated = $this->reportService->getInOutLogs($validated);

        return response()->json([
            'status' => 'success',
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
        ]);
    }

    /**
     * Report 2: Employee and Date-wise Attendance Summary with Total Working Hours
     */
    public function workingHoursReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'emp_id' => 'nullable|string',
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date' => 'nullable|date_format:Y-m-d',
            'search' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|string|in:emp_id,emp_name,designation,date,login_time,logout_time,working_seconds',
            'sort_dir' => 'nullable|string|in:asc,desc',
            'filters' => 'nullable|array',
            'filters.emp_id' => 'nullable|string|max:50',
            'filters.emp_name' => 'nullable|string|max:100',
            'filters.designation' => 'nullable|string|max:100',
            'filters.login_time' => 'nullable|string|max:50',
            'filters.logout_time' => 'nullable|string|max:50',
        ]);

        $paginated = $this->reportService->getWorkingHoursSummary($validated);

        return response()->json([
            'status' => 'success',
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
        ]);
    }
}
