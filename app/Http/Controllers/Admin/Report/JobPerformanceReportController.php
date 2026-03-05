<?php

namespace App\Http\Controllers\Admin\Report;

use App\Enums\ApplicationStatus;
use App\Enums\JobStatus;
use App\Http\Controllers\Controller;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Services\Report\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobPerformanceReportController extends Controller
{
    /**
     * Display the job performance report.
     */
    public function index(Request $request): Response
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (string) $request->input('month', '');

        $data = $this->buildReportData($year, $month);

        return inertia('admin/reports/JobPerformanceReport', [
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
     * Export the job performance report as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (string) $request->input('month', '');

        $data = $this->buildReportData($year, $month);

        $headers = ['Month', 'Total Jobs', 'Total Applications', 'Avg Apps/Job', 'Shortlisted', 'Appointed', 'Confirmed', 'Cancelled Apps', 'Conversion Rate (%)'];
        $rows = collect($data['rows'])->map(fn (array $row): array => [
            $row['label'],
            $row['totalJobs'],
            $row['totalApplications'],
            $row['avgApplicationsPerJob'],
            $row['shortlisted'],
            $row['appointed'],
            $row['confirmed'],
            $row['cancelledApplications'],
            $row['conversionRate'],
        ])->all();

        $summaryRows = [
            [],
            ['Summary'],
            ['Total Jobs Posted', $data['summary']['totalJobs']],
            ['Total Applications', $data['summary']['totalApplications']],
            ['Overall Avg Apps/Job', $data['summary']['avgApplicationsPerJob']],
            ['Overall Conversion Rate', $data['summary']['overallConversionRate'].'%'],
            ['Most Active Month', $data['summary']['mostActiveMonth']],
        ];

        return CsvExportService::export(
            filename: "job-performance-report-{$year}.csv",
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
            ->selectRaw("{$monthExpr} as month_num, COUNT(*) as total")
            ->groupBy('month_num')
            ->pluck('total', 'month_num');

        $appQuery = TuitionJobApplication::query()->whereYear('created_at', $year);

        if ($month !== '' && $month !== 'all') {
            $appQuery->whereMonth('created_at', (int) $month);
        }

        $monthlyApps = (clone $appQuery)
            ->selectRaw("{$monthExpr} as month_num, status, COUNT(*) as total")
            ->groupBy('month_num', 'status')
            ->get();

        $confirmedJobQuery = TuitionJob::query()
            ->where('status', JobStatus::Confirmed)
            ->whereYear('created_at', $year);

        if ($month !== '' && $month !== 'all') {
            $confirmedJobQuery->whereMonth('created_at', (int) $month);
        }

        $monthlyConfirmedJobs = $confirmedJobQuery
            ->selectRaw("{$monthExpr} as month_num, COUNT(*) as total")
            ->groupBy('month_num')
            ->pluck('total', 'month_num');

        $months = $month !== '' && $month !== 'all'
            ? [(int) $month]
            : range(1, 12);

        $rows = [];

        foreach ($months as $m) {
            $totalJobs = (int) ($monthlyJobs[$m] ?? 0);
            $appRecords = $monthlyApps->where('month_num', $m);
            $totalApps = (int) $appRecords->sum('total');

            $shortlisted = (int) $appRecords->where('status', ApplicationStatus::Shortlisted->value)->sum('total');
            $appointed = (int) $appRecords->where('status', ApplicationStatus::Appointed->value)->sum('total');
            $confirmed = (int) $appRecords->where('status', ApplicationStatus::Confirmed->value)->sum('total');
            $cancelled = (int) $appRecords->where('status', ApplicationStatus::Cancelled->value)->sum('total');
            $confirmedJobs = (int) ($monthlyConfirmedJobs[$m] ?? 0);

            $rows[] = [
                'month' => $m,
                'label' => Carbon::create($year, $m, 1)->format('F Y'),
                'totalJobs' => $totalJobs,
                'totalApplications' => $totalApps,
                'avgApplicationsPerJob' => $totalJobs > 0 ? round($totalApps / $totalJobs, 1) : 0,
                'shortlisted' => $shortlisted,
                'appointed' => $appointed,
                'confirmed' => $confirmed,
                'cancelledApplications' => $cancelled,
                'conversionRate' => $totalJobs > 0 ? round(($confirmedJobs / $totalJobs) * 100, 1) : 0,
            ];
        }

        $grandTotalJobs = collect($rows)->sum('totalJobs');
        $grandTotalApps = collect($rows)->sum('totalApplications');
        $grandConfirmed = collect($rows)->sum('confirmed');

        $mostActiveMonth = collect($rows)->sortByDesc('totalJobs')->first();

        return [
            'rows' => $rows,
            'summary' => [
                'totalJobs' => $grandTotalJobs,
                'totalApplications' => $grandTotalApps,
                'avgApplicationsPerJob' => $grandTotalJobs > 0 ? round($grandTotalApps / $grandTotalJobs, 1) : 0,
                'overallConversionRate' => $grandTotalJobs > 0 ? round(($grandConfirmed / $grandTotalJobs) * 100, 1) : 0,
                'mostActiveMonth' => $mostActiveMonth ? $mostActiveMonth['label'] : 'N/A',
            ],
        ];
    }

    /**
     * Get available years from job data.
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
