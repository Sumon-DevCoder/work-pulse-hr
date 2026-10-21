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
            // Fallback for flat query parameters
            'col_emp_id' => 'nullable|string|max:50',
            'col_emp_name' => 'nullable|string|max:100',
            'col_date_time' => 'nullable|string|max:50',
            'col_in_out' => 'nullable|string|max:10',
        ]);

        // Merge flat column filters if passed
        $filters = $validated['filters'] ?? [];
        if ($request->filled('col_emp_id')) $filters['emp_id'] = $request->input('col_emp_id');
        if ($request->filled('col_emp_name')) $filters['emp_name'] = $request->input('col_emp_name');
        if ($request->filled('col_date_time')) $filters['date_time'] = $request->input('col_date_time');
        if ($request->filled('col_in_out')) $filters['in_out'] = $request->input('col_in_out');
        $validated['filters'] = $filters;

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
            'filters.working_hour' => 'nullable|string|max:50',
            // Fallback for flat query parameters
            'col_emp_id' => 'nullable|string|max:50',
            'col_emp_name' => 'nullable|string|max:100',
            'col_designation' => 'nullable|string|max:100',
            'col_login_time' => 'nullable|string|max:50',
            'col_logout_time' => 'nullable|string|max:50',
            'col_working_hour' => 'nullable|string|max:50',
        ]);

        // Merge flat column filters if passed
        $filters = $validated['filters'] ?? [];
        if ($request->filled('col_emp_id')) $filters['emp_id'] = $request->input('col_emp_id');
        if ($request->filled('col_emp_name')) $filters['emp_name'] = $request->input('col_emp_name');
        if ($request->filled('col_designation')) $filters['designation'] = $request->input('col_designation');
        if ($request->filled('col_login_time')) $filters['login_time'] = $request->input('col_login_time');
        if ($request->filled('col_logout_time')) $filters['logout_time'] = $request->input('col_logout_time');
        if ($request->filled('col_working_hour')) $filters['working_hour'] = $request->input('col_working_hour');
        $validated['filters'] = $filters;

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
