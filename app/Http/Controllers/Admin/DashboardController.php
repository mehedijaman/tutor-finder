<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\ContactMessageStatus;
use App\Enums\InvoiceStatus;
use App\Enums\JobStatus;
use App\Enums\RefundStatus;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Invoice;
use App\Models\RefundRequest;
use App\Models\SupportTicket;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with comprehensive platform statistics.
     */
    public function __invoke(): Response
    {
        return inertia('admin/Dashboard', [
            'stats' => $this->gatherStats(),
            'charts' => $this->gatherChartData(),
            'recentActivity' => $this->gatherRecentActivity(),
        ]);
    }

    /**
     * Gather platform-wide statistics.
     *
     * @return array<string, mixed>
     */
    private function gatherStats(): array
    {
        $userCounts = User::query()
            ->whereIn('role', [UserRole::Tutor, UserRole::Guardian])
            ->selectRaw('role, status, COUNT(*) as total')
            ->groupBy('role', 'status')
            ->get();

        $tutorActive = $userCounts->where('role', UserRole::Tutor)->where('status', UserStatus::Active)->sum('total');
        $tutorTotal = $userCounts->where('role', UserRole::Tutor)->sum('total');
        $guardianActive = $userCounts->where('role', UserRole::Guardian)->where('status', UserStatus::Active)->sum('total');
        $guardianTotal = $userCounts->where('role', UserRole::Guardian)->sum('total');

        $jobCounts = TuitionJob::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $applicationCounts = TuitionJobApplication::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $ticketCounts = SupportTicket::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $verificationCounts = VerificationRequest::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $revenueTotal = Invoice::query()
            ->where('status', InvoiceStatus::Paid)
            ->sum('amount');

        $revenueThisMonth = Invoice::query()
            ->where('status', InvoiceStatus::Paid)
            ->where('paid_at', '>=', Carbon::now()->startOfMonth())
            ->sum('amount');

        $unpaidInvoices = Invoice::query()
            ->where('status', InvoiceStatus::Unpaid)
            ->count();

        $pendingRefunds = RefundRequest::query()
            ->where('status', RefundStatus::Pending)
            ->count();

        $openContactMessages = ContactMessage::query()
            ->where('status', ContactMessageStatus::Open)
            ->count();

        return [
            'users' => [
                'totalTutors' => (int) $tutorTotal,
                'activeTutors' => (int) $tutorActive,
                'totalGuardians' => (int) $guardianTotal,
                'activeGuardians' => (int) $guardianActive,
            ],
            'jobs' => [
                'pending' => (int) ($jobCounts[JobStatus::Pending->value] ?? 0),
                'live' => (int) ($jobCounts[JobStatus::Live->value] ?? 0),
                'confirmed' => (int) ($jobCounts[JobStatus::Confirmed->value] ?? 0),
                'cancelled' => (int) ($jobCounts[JobStatus::Cancelled->value] ?? 0),
                'closed' => (int) ($jobCounts[JobStatus::Closed->value] ?? 0),
                'total' => (int) $jobCounts->sum(),
            ],
            'applications' => [
                'applied' => (int) ($applicationCounts[ApplicationStatus::Applied->value] ?? 0),
                'shortlisted' => (int) ($applicationCounts[ApplicationStatus::Shortlisted->value] ?? 0),
                'appointed' => (int) ($applicationCounts[ApplicationStatus::Appointed->value] ?? 0),
                'confirmed' => (int) ($applicationCounts[ApplicationStatus::Confirmed->value] ?? 0),
                'cancelled' => (int) ($applicationCounts[ApplicationStatus::Cancelled->value] ?? 0),
            ],
            'tickets' => [
                'open' => (int) ($ticketCounts[TicketStatus::Open->value] ?? 0),
                'inProgress' => (int) ($ticketCounts[TicketStatus::InProgress->value] ?? 0),
                'closed' => (int) ($ticketCounts[TicketStatus::Closed->value] ?? 0),
            ],
            'verifications' => [
                'pending' => (int) ($verificationCounts[VerificationStatus::Pending->value] ?? 0),
                'approved' => (int) ($verificationCounts[VerificationStatus::Approved->value] ?? 0),
                'verified' => (int) ($verificationCounts[VerificationStatus::Verified->value] ?? 0),
                'rejected' => (int) ($verificationCounts[VerificationStatus::Rejected->value] ?? 0),
            ],
            'finance' => [
                'totalRevenue' => (float) $revenueTotal,
                'monthlyRevenue' => (float) $revenueThisMonth,
                'unpaidInvoices' => $unpaidInvoices,
                'pendingRefunds' => $pendingRefunds,
            ],
            'contactMessages' => [
                'open' => $openContactMessages,
            ],
        ];
    }

    /**
     * Gather chart data for the last 12 months.
     *
     * @return array<string, mixed>
     */
    private function gatherChartData(): array
    {
        $months = collect(range(11, 0))->map(fn (int $i): Carbon => Carbon::now()->subMonths($i)->startOfMonth());

        $labels = $months->map(fn (Carbon $m): string => $m->format('M Y'))->values()->all();

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dateFormatCreated = $isSqlite
            ? "strftime('%Y-%m', created_at) as month"
            : "DATE_FORMAT(created_at, '%Y-%m') as month";
        $dateFormatPaid = $isSqlite
            ? "strftime('%Y-%m', paid_at) as month"
            : "DATE_FORMAT(paid_at, '%Y-%m') as month";

        $userRegistrations = User::query()
            ->whereIn('role', [UserRole::Tutor, UserRole::Guardian])
            ->where('created_at', '>=', $months->first())
            ->selectRaw("{$dateFormatCreated}, role, COUNT(*) as total")
            ->groupBy('month', 'role')
            ->get();

        $jobsCreated = TuitionJob::query()
            ->where('created_at', '>=', $months->first())
            ->selectRaw("{$dateFormatCreated}, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $revenue = Invoice::query()
            ->where('status', InvoiceStatus::Paid)
            ->where('paid_at', '>=', $months->first())
            ->selectRaw("{$dateFormatPaid}, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $refunds = RefundRequest::query()
            ->where('status', RefundStatus::Approved)
            ->where('created_at', '>=', $months->first())
            ->selectRaw("{$dateFormatCreated}, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthKeys = $months->map(fn (Carbon $m): string => $m->format('Y-m'));

        return [
            'labels' => $labels,
            'newTutors' => $monthKeys->map(fn (string $key): int => (int) $userRegistrations->where('month', $key)->where('role', UserRole::Tutor)->sum('total'))->values()->all(),
            'newGuardians' => $monthKeys->map(fn (string $key): int => (int) $userRegistrations->where('month', $key)->where('role', UserRole::Guardian)->sum('total'))->values()->all(),
            'newJobs' => $monthKeys->map(fn (string $key): int => (int) ($jobsCreated[$key] ?? 0))->values()->all(),
            'revenue' => $monthKeys->map(fn (string $key): float => (float) ($revenue[$key] ?? 0))->values()->all(),
            'refunds' => $monthKeys->map(fn (string $key): float => (float) ($refunds[$key] ?? 0))->values()->all(),
        ];
    }

    /**
     * Gather recent activity items.
     *
     * @return array<string, mixed>
     */
    private function gatherRecentActivity(): array
    {
        $recentJobs = TuitionJob::query()
            ->with(['guardian:id,name', 'applications' => fn ($q) => $q->selectRaw('job_id, count(*) as count')->groupBy('job_id')])
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'status', 'guardian_id', 'created_at']);

        $recentTutors = User::query()
            ->where('role', UserRole::Tutor)
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'phone', 'verification_status', 'created_at']);

        $recentGuardians = User::query()
            ->where('role', UserRole::Guardian)
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'phone', 'verification_status', 'created_at']);

        $recentApplications = TuitionJobApplication::query()
            ->with(['tutor:id,name', 'tuitionJob:id,title'])
            ->latest()
            ->limit(5)
            ->get(['id', 'job_id', 'tutor_user_id', 'status', 'created_at']);

        $recentTickets = SupportTicket::query()
            ->with('user:id,name')
            ->latest()
            ->limit(5)
            ->get(['id', 'ticket_number', 'subject', 'status', 'priority', 'user_id', 'created_at']);

        $recentVerifications = VerificationRequest::query()
            ->with('user:id,name,role')
            ->where('status', VerificationStatus::Pending)
            ->latest()
            ->limit(5)
            ->get(['id', 'user_id', 'role', 'status', 'created_at']);

        return [
            'recentJobs' => $recentJobs->map(fn ($job): array => [
                'id' => $job->id,
                'title' => $job->title,
                'status' => $job->status->value,
                'statusLabel' => $job->status->label(),
                'guardian' => $job->guardian?->name ?? 'N/A',
                'applicationsCount' => $job->applications->first()?->count ?? 0,
                'createdAt' => $job->created_at?->diffForHumans(),
            ])->all(),
            'recentTutors' => $recentTutors->map(fn ($tutor): array => [
                'id' => $tutor->id,
                'name' => $tutor->name,
                'email' => $tutor->email,
                'phone' => $tutor->phone,
                'verificationStatus' => $tutor->verification_status?->value ?? 'unverified',
                'createdAt' => $tutor->created_at?->diffForHumans(),
            ])->all(),
            'recentGuardians' => $recentGuardians->map(fn ($g): array => [
                'id' => $g->id,
                'name' => $g->name,
                'email' => $g->email,
                'phone' => $g->phone,
                'verificationStatus' => $g->verification_status?->value ?? 'unverified',
                'createdAt' => $g->created_at?->diffForHumans(),
            ])->all(),
            'recentApplications' => $recentApplications->map(fn ($app): array => [
                'id' => $app->id,
                'jobId' => $app->job_id,
                'jobTitle' => $app->tuitionJob?->title ?? 'N/A',
                'tutorName' => $app->tutor?->name ?? 'N/A',
                'status' => $app->status,
                'createdAt' => $app->created_at?->diffForHumans(),
            ])->all(),
            'recentTickets' => $recentTickets->map(fn ($ticket): array => [
                'id' => $ticket->id,
                'ticketNumber' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'status' => $ticket->status->value,
                'statusLabel' => $ticket->status->label(),
                'priority' => $ticket->priority->value,
                'priorityLabel' => $ticket->priority->label(),
                'user' => $ticket->user?->name ?? 'N/A',
                'createdAt' => $ticket->created_at?->diffForHumans(),
            ])->all(),
            'pendingVerifications' => $recentVerifications->map(fn ($v): array => [
                'id' => $v->id,
                'userName' => $v->user?->name ?? 'N/A',
                'userRole' => $v->role->value,
                'roleLabel' => $v->role->label(),
                'createdAt' => $v->created_at?->diffForHumans(),
            ])->all(),
        ];
    }
}
