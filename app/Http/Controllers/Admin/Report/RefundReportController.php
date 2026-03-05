<?php

namespace App\Http\Controllers\Admin\Report;

use App\Enums\RefundStatus;
use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Services\Report\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RefundReportController extends Controller
{
    /**
     * Display the refund report.
     */
    public function index(Request $request): Response
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (string) $request->input('month', '');
        $status = (string) $request->input('status', '');

        $data = $this->buildReportData($year, $month, $status);

        return inertia('admin/reports/RefundReport', [
            'reportData' => $data['rows'],
            'summary' => $data['summary'],
            'filters' => [
                'year' => $year,
                'month' => $month,
                'status' => $status,
            ],
            'availableYears' => $this->getAvailableYears(),
            'statusOptions' => $this->getStatusOptions(),
        ]);
    }

    /**
     * Export the refund report as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (string) $request->input('month', '');
        $status = (string) $request->input('status', '');

        $data = $this->buildReportData($year, $month, $status);

        $headers = ['Month', 'Total Requests', 'Pending', 'Approved', 'Rejected', 'Paid', 'Total Amount (BDT)', 'Paid Amount (BDT)'];
        $rows = collect($data['rows'])->map(fn (array $row): array => [
            $row['label'],
            $row['totalRequests'],
            $row['pending'],
            $row['approved'],
            $row['rejected'],
            $row['paid'],
            number_format((float) $row['totalAmount'], 2),
            number_format((float) $row['paidAmount'], 2),
        ])->all();

        $summaryRows = [
            [],
            ['Summary'],
            ['Total Requests', $data['summary']['totalRequests']],
            ['Total Requested Amount', number_format((float) $data['summary']['totalRequestedAmount'], 2)],
            ['Total Paid Amount', number_format((float) $data['summary']['totalPaidAmount'], 2)],
            ['Approval Rate', $data['summary']['approvalRate'].'%'],
        ];

        return CsvExportService::export(
            filename: "refund-report-{$year}.csv",
            headers: $headers,
            rows: array_merge($rows, $summaryRows),
        );
    }

    /**
     * Build the report data for the given filters.
     *
     * @return array{rows: list<array<string, mixed>>, summary: array<string, mixed>}
     */
    private function buildReportData(int $year, string $month, string $status): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $monthExpr = $isSqlite
            ? "CAST(strftime('%m', requested_at) AS INTEGER)"
            : 'MONTH(requested_at)';

        $query = RefundRequest::query()->whereYear('requested_at', $year);

        if ($month !== '' && $month !== 'all') {
            $query->whereMonth('requested_at', (int) $month);
        }

        if ($status !== '' && $status !== 'all' && RefundStatus::tryFrom($status) !== null) {
            $query->where('status', $status);
        }

        $monthlyData = (clone $query)
            ->selectRaw("{$monthExpr} as month_num, status, COUNT(*) as total, SUM(amount) as total_amount")
            ->groupBy('month_num', 'status')
            ->get();

        $months = $month !== '' && $month !== 'all'
            ? [(int) $month]
            : range(1, 12);

        $rows = [];

        foreach ($months as $m) {
            $monthRecords = $monthlyData->where('month_num', $m);

            $rows[] = [
                'month' => $m,
                'label' => Carbon::create($year, $m, 1)->format('F Y'),
                'totalRequests' => (int) $monthRecords->sum('total'),
                'pending' => (int) $monthRecords->where('status', RefundStatus::Pending->value)->sum('total'),
                'approved' => (int) $monthRecords->where('status', RefundStatus::Approved->value)->sum('total'),
                'rejected' => (int) $monthRecords->where('status', RefundStatus::Rejected->value)->sum('total'),
                'paid' => (int) $monthRecords->where('status', RefundStatus::Paid->value)->sum('total'),
                'totalAmount' => (float) $monthRecords->sum('total_amount'),
                'paidAmount' => (float) $monthRecords->where('status', RefundStatus::Paid->value)->sum('total_amount'),
            ];
        }

        $grandTotal = collect($rows)->sum('totalRequests');
        $grandApproved = collect($rows)->sum('approved') + collect($rows)->sum('paid');

        return [
            'rows' => $rows,
            'summary' => [
                'totalRequests' => $grandTotal,
                'totalRequestedAmount' => collect($rows)->sum('totalAmount'),
                'totalPaidAmount' => collect($rows)->sum('paidAmount'),
                'approvalRate' => $grandTotal > 0 ? round(($grandApproved / $grandTotal) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * Get available years from refund request data.
     *
     * @return list<int>
     */
    private function getAvailableYears(): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $yearExpr = $isSqlite
            ? "CAST(strftime('%Y', requested_at) AS INTEGER)"
            : 'YEAR(requested_at)';

        $years = RefundRequest::query()
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

    /**
     * Get refund status options for filter.
     *
     * @return list<array{value: string, label: string}>
     */
    private function getStatusOptions(): array
    {
        return collect(RefundStatus::cases())->map(fn (RefundStatus $s): array => [
            'value' => $s->value,
            'label' => ucfirst($s->value),
        ])->all();
    }
}
