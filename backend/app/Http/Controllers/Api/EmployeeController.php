<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        protected AttendanceReportService $reportService
    ) {}

    /**
     * Searchable employee list for dropdowns.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $limit = min((int) ($request->query('limit', 50)), 100);

        $employees = $this->reportService->getEmployees($search, $limit);

        return response()->json([
            'status' => 'success',
            'data' => $employees,
        ]);
    }
}
