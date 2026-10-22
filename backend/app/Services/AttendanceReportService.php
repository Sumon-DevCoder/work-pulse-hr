<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceReportService
{
    /**
     * Report 1: Employee and Date-wise Attendance In-Out Log Report
     * Supports Global Search, Date Range, Employee Filter, and Column-level Filters.
     */
    public function getInOutLogs(array $params): LengthAwarePaginator
    {
        $page = (int) ($params['page'] ?? 1);
        $perPage = min((int) ($params['per_page'] ?? 25), 100);
        $empId = $params['emp_id'] ?? null;
        $fromDate = $params['from_date'] ?? null;
        $toDate = $params['to_date'] ?? null;
        $search = trim($params['search'] ?? '');
        $sortBy = $params['sort_by'] ?? 'date_time';
        $sortDir = strtolower($params['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $columnFilters = $params['filters'] ?? [];

        // Fallback default date range if neither date nor employee is specified (prevents full 1.1M unconstrained scan)
        if (empty($fromDate) && empty($toDate) && empty($empId)) {
            $fromDate = '2026-01-01';
            $toDate = '2026-01-01';
        }

        $query = DB::table('tbl_attendance_log as l')
            ->leftJoin('tbl_employee as e', 'e.id', '=', 'l.emp_id')
            ->select([
                'l.id',
                'l.emp_id',
                DB::raw("COALESCE(e.emp_name, CONCAT('Employee #', l.emp_id)) as emp_name"),
                'l.Date as date',
                'l.date_time',
                'l.in_out',
                'l.terminal_id',
                'l.attendance_type',
            ]);

        // 1. Employee Filter (Specific ID or Name or ALL)
        if (!empty($empId) && strtolower($empId) !== 'all') {
            $query->where(function ($q) use ($empId) {
                $q->where('l.emp_id', $empId)
                  ->orWhere('e.emp_name', 'like', "%{$empId}%");
            });
        }

        // 2. Date Range Filter
        if (!empty($fromDate)) {
            $query->where('l.Date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->where('l.Date', '<=', $toDate);
        }

        // 3. Global Search across all visible columns
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('l.emp_id', 'like', "%{$search}%")
                  ->orWhere('e.emp_name', 'like', "%{$search}%")
                  ->orWhere('l.date_time', 'like', "%{$search}%")
                  ->orWhere('l.in_out', 'like', "%{$search}%");
            });
        }

        // 4. Individual Column Filters
        if (!empty($columnFilters['emp_id'])) {
            $query->where('l.emp_id', 'like', "%{$columnFilters['emp_id']}%");
        }
        if (!empty($columnFilters['emp_name'])) {
            $query->where(function ($q) use ($columnFilters) {
                $q->where('e.emp_name', 'like', "%{$columnFilters['emp_name']}%")
                  ->orWhere('l.emp_id', 'like', "%{$columnFilters['emp_name']}%");
            });
        }
        if (!empty($columnFilters['date_time'])) {
            $query->where('l.date_time', 'like', "%{$columnFilters['date_time']}%");
        }
        if (!empty($columnFilters['in_out'])) {
            $val = strtoupper($columnFilters['in_out']);
            if ($val === 'IN') {
                $query->where('l.in_out', 'logIN');
            } elseif ($val === 'OUT') {
                $query->where('l.in_out', 'logOUT');
            } else {
                $query->where('l.in_out', 'like', "%{$columnFilters['in_out']}%");
            }
        }

        // 5. Sorting
        $allowedSorts = [
            'emp_id' => 'l.emp_id',
            'emp_name' => 'emp_name',
            'date_time' => 'l.date_time',
            'in_out' => 'l.in_out',
        ];
        $sortColumn = $allowedSorts[$sortBy] ?? 'l.date_time';
        $query->orderBy($sortColumn, $sortDir);

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        // Normalize IN / OUT status for UI
        $paginated->getCollection()->transform(function ($item, $key) use ($paginated) {
            $item->sl = ($paginated->currentPage() - 1) * $paginated->perPage() + $key + 1;
            $rawStatus = strtoupper((string) $item->in_out);
            $item->in_out_status = str_contains($rawStatus, 'IN') ? 'IN' : 'OUT';
            $item->date_time_12h = !empty($item->date_time) ? date('h:i:s A', strtotime($item->date_time)) : null;
            return $item;
        });

        return $paginated;
    }

    /**
     * Report 2: Employee and Date-wise Attendance Summary with Total Working Hours.
     * Uses optimized single-pass SQL aggregation.
     */
    public function getWorkingHoursSummary(array $params): LengthAwarePaginator
    {
        $page = (int) ($params['page'] ?? 1);
        $perPage = min((int) ($params['per_page'] ?? 25), 100);
        $empId = $params['emp_id'] ?? null;
        $fromDate = $params['from_date'] ?? null;
        $toDate = $params['to_date'] ?? null;
        $search = trim($params['search'] ?? '');
        $sortBy = $params['sort_by'] ?? 'date';
        $sortDir = strtolower($params['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $columnFilters = $params['filters'] ?? [];

        // Fallback default date range if neither date nor employee is specified (prevents full 1.1M unconstrained group-by)
        if (empty($fromDate) && empty($toDate) && empty($empId)) {
            $fromDate = '2026-01-01';
            $toDate = '2026-01-30';
        }

        // Build base aggregation subquery
        $query = DB::table('tbl_attendance_log as l')
            ->leftJoin('tbl_employee as e', 'e.id', '=', 'l.emp_id')
            ->select([
                'l.emp_id',
                DB::raw("COALESCE(e.emp_name, CONCAT('Employee #', l.emp_id)) as emp_name"),
                DB::raw("COALESCE(e.designation, 'Operator') as designation"),
                'l.Date as date',
                DB::raw("MIN(CASE WHEN l.in_out = 'logIN' THEN l.date_time END) AS login_time"),
                DB::raw("MAX(CASE WHEN l.in_out = 'logOUT' THEN l.date_time END) AS logout_time"),
                DB::raw("TIMESTAMPDIFF(SECOND, MIN(CASE WHEN l.in_out = 'logIN' THEN l.date_time END), MAX(CASE WHEN l.in_out = 'logOUT' THEN l.date_time END)) AS working_seconds"),
            ])
            ->groupBy('l.emp_id', 'l.Date', 'e.emp_name', 'e.designation');

        // 1. Employee Filter
        if (!empty($empId) && strtolower($empId) !== 'all') {
            $query->where(function ($q) use ($empId) {
                $q->where('l.emp_id', $empId)
                  ->orWhere('e.emp_name', 'like', "%{$empId}%");
            });
        }

        // 2. Date Range
        if (!empty($fromDate)) {
            $query->where('l.Date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->where('l.Date', '<=', $toDate);
        }

        // Wrap into an outer query for flexible searching on aggregated fields
        $outerQuery = DB::table(DB::raw("({$query->toSql()}) as summary"))
            ->mergeBindings($query);

        // 3. Global Search
        if (!empty($search)) {
            $outerQuery->where(function ($q) use ($search) {
                $q->where('emp_id', 'like', "%{$search}%")
                  ->orWhere('emp_name', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('login_time', 'like', "%{$search}%")
                  ->orWhere('logout_time', 'like', "%{$search}%");
            });
        }

        // 4. Column-level Filters
        if (!empty($columnFilters['emp_id'])) {
            $outerQuery->where('emp_id', 'like', "%{$columnFilters['emp_id']}%");
        }
        if (!empty($columnFilters['emp_name'])) {
            $outerQuery->where('emp_name', 'like', "%{$columnFilters['emp_name']}%");
        }
        if (!empty($columnFilters['designation'])) {
            $outerQuery->where('designation', 'like', "%{$columnFilters['designation']}%");
        }
        if (!empty($columnFilters['login_time'])) {
            $outerQuery->where('login_time', 'like', "%{$columnFilters['login_time']}%");
        }
        if (!empty($columnFilters['logout_time'])) {
            $outerQuery->where('logout_time', 'like', "%{$columnFilters['logout_time']}%");
        }
        if (!empty($columnFilters['working_hour'])) {
            $term = trim($columnFilters['working_hour']);
            if (is_numeric($term)) {
                $outerQuery->where('working_seconds', '>=', (float) $term * 3600);
            } else {
                preg_match('/(\d+(?:\.\d+)?)/', $term, $matches);
                if (!empty($matches[1])) {
                    $outerQuery->where('working_seconds', '>=', (float) $matches[1] * 3600);
                }
            }
        }

        // 5. Sorting
        $allowedSorts = [
            'emp_id' => 'emp_id',
            'emp_name' => 'emp_name',
            'designation' => 'designation',
            'date' => 'date',
            'login_time' => 'login_time',
            'logout_time' => 'logout_time',
            'working_seconds' => 'working_seconds',
        ];
        $sortColumn = $allowedSorts[$sortBy] ?? 'date';
        $outerQuery->orderBy($sortColumn, $sortDir)->orderBy('emp_id', 'asc');

        $paginated = $outerQuery->paginate($perPage, ['*'], 'page', $page);

        // Format working hours into "X hrs Y mins"
        $paginated->getCollection()->transform(function ($item, $key) use ($paginated) {
            $item->sl = ($paginated->currentPage() - 1) * $paginated->perPage() + $key + 1;
            
            $seconds = (int) ($item->working_seconds ?? 0);
            if ($seconds > 0) {
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $item->working_hour = sprintf('%d hrs %02d mins', $hours, $minutes);
                $item->working_hours_decimal = round($seconds / 3600, 2);
            } else {
                $item->working_hour = 'N/A';
                $item->working_hours_decimal = 0.0;
            }

            $item->login_time_12h = !empty($item->login_time) ? date('h:i:s A', strtotime($item->login_time)) : null;
            $item->logout_time_12h = !empty($item->logout_time) ? date('h:i:s A', strtotime($item->logout_time)) : null;

            return $item;
        });

        return $paginated;
    }

    /**
     * Dashboard 1: Dynamic Bar Chart Feed
     * Displays total working hours per day based on date range.
     * Default: Last 30 days.
     * Dynamic bars: matching selected date count (e.g. 10 days = 10 bars, 120 days = 120 bars).
     */
    public function getDashboardAnalytics(?string $fromDate, ?string $toDate): array
    {
        // Default to the last 30 days of data boundary
        if (empty($fromDate) || empty($toDate)) {
            $toDate = '2026-09-17';
            $fromDate = '2026-08-19';
        }

        $startDate = Carbon::parse($fromDate)->startOfDay();
        $endDate = Carbon::parse($toDate)->startOfDay();

        // Calculate total days
        $totalDays = (int) $startDate->diffInDays($endDate) + 1;

        try {
            $subQuery = DB::table('tbl_attendance_log')
                ->selectRaw('`Date` as `work_date`, `emp_id`, TIMESTAMPDIFF(SECOND, MIN(CASE WHEN `in_out` = "logIN" THEN `date_time` END), MAX(CASE WHEN `in_out` = "logOUT" THEN `date_time` END)) AS `day_seconds`')
                ->whereBetween('Date', [$fromDate, $toDate])
                ->groupBy('Date', 'emp_id');

            $dailyTotals = DB::query()->fromSub($subQuery, 'daily_emp')
                ->selectRaw('`work_date` as `date`, ROUND(COALESCE(SUM(`day_seconds`), 0) / 3600, 2) as `total_working_hours`, COUNT(DISTINCT `emp_id`) as `total_active_employees`')
                ->groupBy('work_date')
                ->orderBy('work_date', 'asc')
                ->get()
                ->keyBy('date');
        } catch (\Throwable $e) {
            \Log::error('Dashboard analytics error: ' . $e->getMessage());
            $dailyTotals = collect();
        }

        // Ensure every single date in the range has a bar (zero-filled if no punches)
        $chartData = [];
        $current = $startDate->copy();
        $grandTotalHours = 0.0;
        $maxHours = 0.0;

        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();
            $record = $dailyTotals->get($dateStr);

            $hours = $record ? (float) $record->total_working_hours : 0.0;
            $activeEmp = $record ? (int) $record->total_active_employees : 0;

            $chartData[] = [
                'date' => $dateStr,
                'formatted_date' => $current->format('M d, Y'),
                'short_date' => $current->format('d M'),
                'total_working_hours' => $hours,
                'total_active_employees' => $activeEmp,
            ];

            $grandTotalHours += $hours;
            if ($hours > $maxHours) {
                $maxHours = $hours;
            }

            $current->addDay();
        }

        return [
            'date_range' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'total_days' => $totalDays,
            ],
            'summary' => [
                'grand_total_hours' => round($grandTotalHours, 2),
                'average_daily_hours' => $totalDays > 0 ? round($grandTotalHours / $totalDays, 2) : 0,
                'peak_day_hours' => round($maxHours, 2),
            ],
            'bars_count' => count($chartData),
            'chart_data' => $chartData,
            'refreshed_at' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Searchable Employee List for Combobox / Dropdown.
     */
    public function getEmployees(?string $search = '', int $limit = 50): Collection
    {
        $search = trim($search ?? '');

        // Query tbl_employee
        $query = DB::table('tbl_employee')
            ->select([
                'id',
                'emp_id',
                'emp_name',
                'designation',
                'card_id',
            ]);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('emp_id', 'like', "%{$search}%")
                  ->orWhere('emp_name', 'like', "%{$search}%");
            });
        }

        $results = $query->limit($limit)->get();

        // Also query distinct IDs from attendance logs if search is a specific ID
        if (!empty($search) && $results->isEmpty()) {
            $queryLog = DB::table('tbl_attendance_log');
            if (is_numeric($search)) {
                $queryLog->where('emp_id', 'like', "{$search}%");
            } else {
                $queryLog->where('emp_id', $search);
            }
            $logIds = $queryLog->distinct()->limit(20)->pluck('emp_id');

            return $logIds->map(function ($id) {
                return (object) [
                    'id' => $id,
                    'emp_id' => $id,
                    'emp_name' => "Employee #{$id}",
                    'designation' => 'Staff',
                    'card_id' => null,
                ];
            });
        }

        return $results;
    }
}
