<?php

namespace App\Http\Controllers\Admin\Report;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Report\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncomeReportController extends Controller
{
    /**
     * Display the monthly income report.
     */
    public function index(Request $request): Response
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (string) $request->input('month', '');
        $type = (string) $request->input('type', '');

        $availableYears = $this->getAvailableYears();
        $data = $this->buildReportData($year, $month, $type);

        return inertia('admin/reports/IncomeReport', [
            'reportData' => $data['rows'],
            'summary' => $data['summary'],
            'filters' => [
                'year' => $year,
                'month' => $month,
                'type' => $type,
            ],
            'availableYears' => $availableYears,
            'typeOptions' => $this->getTypeOptions(),
        ]);
    }

    /**
     * Export the income report as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (string) $request->input('month', '');
        $type = (string) $request->input('type', '');

        $data = $this->buildReportData($year, $month, $type);

        $headers = ['Month', 'Service Fee', 'Verification Fee', 'Escrow', 'Total Income', 'Paid Count', 'Unpaid Count'];
        $rows = collect($data['rows'])->map(fn (array $row): array => [
            $row['label'],
            number_format((float) $row['serviceFee'], 2),
            number_format((float) $row['verificationFee'], 2),
            number_format((float) $row['escrow'], 2),
            number_format((float) $row['total'], 2),
            $row['paidCount'],
            $row['unpaidCount'],
        ])->all();

        $summaryRows = [
            [],
            ['Summary'],
            ['Total Income', number_format((float) $data['summary']['totalIncome'], 2)],
            ['Total Paid', number_format((float) $data['summary']['totalPaid'], 2)],
            ['Total Unpaid', number_format((float) $data['summary']['totalUnpaid'], 2)],
            ['Total Invoices', $data['summary']['totalInvoices']],
        ];

        return CsvExportService::export(
            filename: "income-report-{$year}.csv",
            headers: $headers,
            rows: array_merge($rows, $summaryRows),
        );
    }

    /**
     * Build the report data for the given filters.
     *
     * @return array{rows: list<array<string, mixed>>, summary: array<string, mixed>}
     */
    private function buildReportData(int $year, string $month, string $type): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        $query = Invoice::query()
            ->whereYear('created_at', $year);

        if ($month !== '' && $month !== 'all') {
            $query->whereMonth('created_at', (int) $month);
        }

        if ($type !== '' && $type !== 'all' && InvoiceType::tryFrom($type) !== null) {
            $query->where('type', $type);
        }

        $dateFormat = $isSqlite
            ? "CAST(strftime('%m', created_at) AS INTEGER) as month_num"
            : 'MONTH(created_at) as month_num';

        $monthlyData = (clone $query)
            ->selectRaw("{$dateFormat}, type, status, COUNT(*) as invoice_count, SUM(amount) as total_amount")
            ->groupBy('month_num', 'type', 'status')
            ->get();

        $months = $month !== '' && $month !== 'all'
            ? [(int) $month]
            : range(1, 12);

        $rows = [];

        foreach ($months as $m) {
            $monthRecords = $monthlyData->where('month_num', $m);
            $paidRecords = $monthRecords->where('status', InvoiceStatus::Paid->value);

            $serviceFee = (float) $paidRecords->where('type', InvoiceType::PlatformServiceFee->value)->sum('total_amount');
            $verificationFee = (float) $paidRecords
                ->whereIn('type', [InvoiceType::TutorVerificationFee->value, InvoiceType::GuardianVerificationFee->value])
                ->sum('total_amount');
            $escrow = (float) $paidRecords->where('type', InvoiceType::OnlineMonth1Escrow->value)->sum('total_amount');

            $paidCount = (int) $monthRecords->where('status', InvoiceStatus::Paid->value)->sum('invoice_count');
            $unpaidCount = (int) $monthRecords->where('status', InvoiceStatus::Unpaid->value)->sum('invoice_count');

            $rows[] = [
                'month' => $m,
                'label' => Carbon::create($year, $m, 1)->format('F Y'),
                'serviceFee' => $serviceFee,
                'verificationFee' => $verificationFee,
                'escrow' => $escrow,
                'total' => $serviceFee + $verificationFee + $escrow,
                'paidCount' => $paidCount,
                'unpaidCount' => $unpaidCount,
            ];
        }

        $totalIncome = collect($rows)->sum('total');
        $totalPaid = Invoice::query()
            ->whereYear('created_at', $year)
            ->where('status', InvoiceStatus::Paid)
            ->sum('amount');
        $totalUnpaid = Invoice::query()
            ->whereYear('created_at', $year)
            ->where('status', InvoiceStatus::Unpaid)
            ->sum('amount');
        $totalInvoices = Invoice::query()
            ->whereYear('created_at', $year)
            ->count();

        return [
            'rows' => $rows,
            'summary' => [
                'totalIncome' => (float) $totalIncome,
                'totalPaid' => (float) $totalPaid,
                'totalUnpaid' => (float) $totalUnpaid,
                'totalInvoices' => $totalInvoices,
                'currency' => 'BDT',
            ],
        ];
    }

    /**
     * Get available years from invoice data.
     *
     * @return list<int>
     */
    private function getAvailableYears(): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $yearExpr = $isSqlite
            ? "CAST(strftime('%Y', created_at) AS INTEGER)"
            : 'YEAR(created_at)';

        $years = Invoice::query()
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
     * Get invoice type options for filter.
     *
     * @return list<array{value: string, label: string}>
     */
    private function getTypeOptions(): array
    {
        return collect(InvoiceType::cases())->map(fn (InvoiceType $t): array => [
            'value' => $t->value,
            'label' => $t->label(),
        ])->all();
    }
}
