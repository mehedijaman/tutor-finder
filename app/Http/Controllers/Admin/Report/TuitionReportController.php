<?php

namespace App\Http\Controllers\Admin\Report;

use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Models\TuitionJob;
use App\Models\TuitionJobAssignment;
use App\Services\Report\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TuitionReportController extends Controller
{
    /**
     * Display the monthly tuition report.
     */
    public function index(Request $request): Response
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (string) $request->input('month', '');

        $data = $this->buildReportData($year, $month);

        return inertia('admin/reports/TuitionReport', [
            'reportData' => $data['rows'],
            'summary' => $data['summary'],
            'filters' => [
                'year' => $year,
                'month' => $month,
            ],
            'availableYears' => $this->getAvailableYears(),
        ]);
    }

    /**
     * Export the tuition report as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (string) $request->input('month', '');

        $data = $this->buildReportData($year, $month);

        $headers = ['Month', 'Total Jobs', 'Pending', 'Live', 'Confirmed', 'Cancelled', 'Closed', 'Assignments', 'Total Salary (BDT)', 'Avg Salary (BDT)'];
        $rows = collect($data['rows'])->map(fn (array $row): array => [
            $row['label'],
            $row['totalJobs'],
            $row['pending'],
            $row['live'],
            $row['confirmed'],
            $row['cancelled'],
            $row['closed'],
            $row['assignments'],
            number_format((float) $row['totalSalary'], 2),
            number_format((float) $row['avgSalary'], 2),
        ])->all();

        $summaryRows = [
            [],
            ['Summary'],
            ['Total Jobs', $data['summary']['totalJobs']],
            ['Total Assignments', $data['summary']['totalAssignments']],
            ['Total Salary', number_format((float) $data['summary']['totalSalary'], 2)],
            ['Avg Salary', number_format((float) $data['summary']['avgSalary'], 2)],
            ['Confirmation Rate', $data['summary']['confirmationRate'].'%'],
        ];

        return CsvExportService::export(
            filename: "tuition-report-{$year}.csv",
            headers: $headers,
            rows: array_merge($rows, $summaryRows),
        );
    }

    /**
     * Build the report data for the given filters.
     *
     * @return array{rows: list<array<string, mixed>>, summary: array<string, mixed>}
     */
    private function buildReportData(int $year, string $month): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $monthExpr = $isSqlite
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';

        $jobQuery = TuitionJob::query()->whereYear('created_at', $year);

        if ($month !== '' && $month !== 'all') {
            $jobQuery->whereMonth('created_at', (int) $month);
        }

        $monthlyJobs = (clone $jobQuery)
            ->selectRaw("{$monthExpr} as month_num, status, COUNT(*) as total")
            ->groupBy('month_num', 'status')
            ->get();

        $assignmentMonthExpr = $isSqlite
            ? "CAST(strftime('%m', appointed_at) AS INTEGER)"
            : 'MONTH(appointed_at)';

        $assignmentQuery = TuitionJobAssignment::query()->whereYear('appointed_at', $year);

        if ($month !== '' && $month !== 'all') {
            $assignmentQuery->whereMonth('appointed_at', (int) $month);
        }

        $monthlyAssignments = $assignmentQuery
            ->selectRaw("{$assignmentMonthExpr} as month_num, COUNT(*) as total, SUM(salary_base_amount) as total_salary")
            ->groupBy('month_num')
            ->get();

        $months = $month !== '' && $month !== 'all'
            ? [(int) $month]
            : range(1, 12);

        $rows = [];

        foreach ($months as $m) {
            $jobRecords = $monthlyJobs->where('month_num', $m);
            $assignmentRecord = $monthlyAssignments->firstWhere('month_num', $m);

            $totalJobs = (int) $jobRecords->sum('total');
            $assignmentCount = (int) ($assignmentRecord?->total ?? 0);
            $totalSalary = (float) ($assignmentRecord?->total_salary ?? 0);

            $rows[] = [
                'month' => $m,
                'label' => Carbon::create($year, $m, 1)->format('F Y'),
                'totalJobs' => $totalJobs,
                'pending' => (int) $jobRecords->where('status', JobStatus::Pending->value)->sum('total'),
                'live' => (int) $jobRecords->where('status', JobStatus::Live->value)->sum('total'),
                'confirmed' => (int) $jobRecords->where('status', JobStatus::Confirmed->value)->sum('total'),
                'cancelled' => (int) $jobRecords->where('status', JobStatus::Cancelled->value)->sum('total'),
                'closed' => (int) $jobRecords->where('status', JobStatus::Closed->value)->sum('total'),
                'assignments' => $assignmentCount,
                'totalSalary' => $totalSalary,
                'avgSalary' => $assignmentCount > 0 ? round($totalSalary / $assignmentCount, 2) : 0,
            ];
        }

        $grandTotalJobs = collect($rows)->sum('totalJobs');
        $grandTotalAssignments = collect($rows)->sum('assignments');
        $grandTotalSalary = collect($rows)->sum('totalSalary');
        $grandConfirmed = collect($rows)->sum('confirmed');

        return [
            'rows' => $rows,
            'summary' => [
                'totalJobs' => $grandTotalJobs,
                'totalAssignments' => $grandTotalAssignments,
                'totalSalary' => $grandTotalSalary,
                'avgSalary' => $grandTotalAssignments > 0 ? round($grandTotalSalary / $grandTotalAssignments, 2) : 0,
                'confirmationRate' => $grandTotalJobs > 0 ? round(($grandConfirmed / $grandTotalJobs) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * Get available years from tuition job data.
     *
     * @return list<int>
     */
    private function getAvailableYears(): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $yearExpr = $isSqlite
            ? "CAST(strftime('%Y', created_at) AS INTEGER)"
            : 'YEAR(created_at)';

        $years = TuitionJob::query()
            ->selectRaw("{$yearExpr} as year")
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->map(fn ($y): int => (int) $y)
            ->all();

        $currentYear = Carbon::now()->year;
        if (! in_array($currentYear, $years, true)) {
            array_unshift($years, $currentYear);
        }

        return $years;
    }
}
