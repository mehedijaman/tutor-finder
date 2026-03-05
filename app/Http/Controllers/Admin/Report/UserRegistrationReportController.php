<?php

namespace App\Http\Controllers\Admin\Report;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Report\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserRegistrationReportController extends Controller
{
    /**
     * Display the user registration report.
     */
    public function index(Request $request): Response
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (string) $request->input('month', '');
        $role = (string) $request->input('role', '');

        $data = $this->buildReportData($year, $month, $role);

        return inertia('admin/reports/UserRegistrationReport', [
            'reportData' => $data['rows'],
            'summary' => $data['summary'],
            'filters' => [
                'year' => $year,
                'month' => $month,
                'role' => $role,
            ],
            'availableYears' => $this->getAvailableYears(),
        ]);
    }

    /**
     * Export the user registration report as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (string) $request->input('month', '');
        $role = (string) $request->input('role', '');

        $data = $this->buildReportData($year, $month, $role);

        $headers = ['Month', 'New Tutors', 'New Guardians', 'Total Registrations', 'Active Tutors', 'Active Guardians', 'Verified Tutors', 'Verified Guardians'];
        $rows = collect($data['rows'])->map(fn (array $row): array => [
            $row['label'],
            $row['newTutors'],
            $row['newGuardians'],
            $row['totalRegistrations'],
            $row['activeTutors'],
            $row['activeGuardians'],
            $row['verifiedTutors'],
            $row['verifiedGuardians'],
        ])->all();

        $summaryRows = [
            [],
            ['Summary'],
            ['Total Registrations', $data['summary']['totalRegistrations']],
            ['Total Tutors', $data['summary']['totalTutors']],
            ['Total Guardians', $data['summary']['totalGuardians']],
            ['Active Users', $data['summary']['totalActive']],
            ['Verified Users', $data['summary']['totalVerified']],
        ];

        return CsvExportService::export(
            filename: "user-registration-report-{$year}.csv",
            headers: $headers,
            rows: array_merge($rows, $summaryRows),
        );
    }

    /**
     * Build the report data for the given filters.
     *
     * @return array{rows: list<array<string, mixed>>, summary: array<string, mixed>}
     */
    private function buildReportData(int $year, string $month, string $role): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $monthExpr = $isSqlite
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';

        $query = User::query()
            ->whereIn('role', [UserRole::Tutor, UserRole::Guardian])
            ->whereYear('created_at', $year);

        if ($month !== '' && $month !== 'all') {
            $query->whereMonth('created_at', (int) $month);
        }

        if ($role !== '' && $role !== 'all' && in_array($role, ['tutor', 'guardian'], true)) {
            $query->where('role', $role);
        }

        $monthlyRegistrations = (clone $query)
            ->selectRaw("{$monthExpr} as month_num, role, status, verification_status, COUNT(*) as total")
            ->groupBy('month_num', 'role', 'status', 'verification_status')
            ->get();

        $months = $month !== '' && $month !== 'all'
            ? [(int) $month]
            : range(1, 12);

        $rows = [];

        foreach ($months as $m) {
            $monthRecords = $monthlyRegistrations->where('month_num', $m);

            $newTutors = (int) $monthRecords->where('role', UserRole::Tutor->value)->sum('total');
            $newGuardians = (int) $monthRecords->where('role', UserRole::Guardian->value)->sum('total');

            $activeTutors = (int) $monthRecords
                ->where('role', UserRole::Tutor->value)
                ->where('status', UserStatus::Active->value)
                ->sum('total');
            $activeGuardians = (int) $monthRecords
                ->where('role', UserRole::Guardian->value)
                ->where('status', UserStatus::Active->value)
                ->sum('total');

            $verifiedTutors = (int) $monthRecords
                ->where('role', UserRole::Tutor->value)
                ->where('verification_status', 'verified')
                ->sum('total');
            $verifiedGuardians = (int) $monthRecords
                ->where('role', UserRole::Guardian->value)
                ->where('verification_status', 'verified')
                ->sum('total');

            $rows[] = [
                'month' => $m,
                'label' => Carbon::create($year, $m, 1)->format('F Y'),
                'newTutors' => $newTutors,
                'newGuardians' => $newGuardians,
                'totalRegistrations' => $newTutors + $newGuardians,
                'activeTutors' => $activeTutors,
                'activeGuardians' => $activeGuardians,
                'verifiedTutors' => $verifiedTutors,
                'verifiedGuardians' => $verifiedGuardians,
            ];
        }

        return [
            'rows' => $rows,
            'summary' => [
                'totalRegistrations' => collect($rows)->sum('totalRegistrations'),
                'totalTutors' => collect($rows)->sum('newTutors'),
                'totalGuardians' => collect($rows)->sum('newGuardians'),
                'totalActive' => collect($rows)->sum('activeTutors') + collect($rows)->sum('activeGuardians'),
                'totalVerified' => collect($rows)->sum('verifiedTutors') + collect($rows)->sum('verifiedGuardians'),
            ],
        ];
    }

    /**
     * Get available years from user data.
     *
     * @return list<int>
     */
    private function getAvailableYears(): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $yearExpr = $isSqlite
            ? "CAST(strftime('%Y', created_at) AS INTEGER)"
            : 'YEAR(created_at)';

        $years = User::query()
            ->whereIn('role', [UserRole::Tutor, UserRole::Guardian])
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
