<?php

use App\Enums\ContactMessageStatus;
use App\Enums\InvoiceStatus;
use App\Enums\JobStatus;
use App\Enums\RefundStatus;
use App\Enums\TicketStatus;
use App\Enums\VerificationStatus;
use App\Models\ContactMessage;
use App\Models\Invoice;
use App\Models\RefundRequest;
use App\Models\SupportTicket;
use App\Models\TuitionJob;
use App\Models\User;
use App\Models\VerificationRequest;

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();
});

it('renders the admin dashboard with all required prop sections', function (): void {
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Dashboard')
            ->has('stats')
            ->has('stats.users')
            ->has('stats.jobs')
            ->has('stats.applications')
            ->has('stats.tickets')
            ->has('stats.verifications')
            ->has('stats.finance')
            ->has('stats.contactMessages')
            ->has('charts')
            ->has('charts.labels')
            ->has('charts.newTutors')
            ->has('charts.newGuardians')
            ->has('charts.newJobs')
            ->has('charts.revenue')
            ->has('recentActivity')
            ->has('recentActivity.recentJobs')
            ->has('recentActivity.recentTickets')
            ->has('recentActivity.pendingVerifications'));
});

it('returns correct user counts by role', function (): void {
    User::factory()->tutor()->count(3)->create();
    User::factory()->guardian()->count(2)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.users.totalTutors', 3)
            ->where('stats.users.totalGuardians', 2));
});

it('returns correct job status counts', function (): void {
    TuitionJob::factory()->count(2)->create(['status' => JobStatus::Pending]);
    TuitionJob::factory()->count(3)->create(['status' => JobStatus::Live, 'published_at' => now()]);
    TuitionJob::factory()->count(1)->create(['status' => JobStatus::Confirmed]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.jobs.pending', 2)
            ->where('stats.jobs.live', 3)
            ->where('stats.jobs.confirmed', 1)
            ->where('stats.jobs.total', 6));
});

it('returns correct ticket status counts', function (): void {
    SupportTicket::factory()->count(3)->create(['status' => TicketStatus::Open]);
    SupportTicket::factory()->count(1)->create(['status' => TicketStatus::InProgress]);
    SupportTicket::factory()->count(2)->create(['status' => TicketStatus::Closed, 'closed_at' => now(), 'closed_by' => $this->admin->id]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.tickets.open', 3)
            ->where('stats.tickets.inProgress', 1)
            ->where('stats.tickets.closed', 2));
});

it('returns correct verification counts', function (): void {
    VerificationRequest::factory()->count(2)->create(['status' => VerificationStatus::Pending]);
    VerificationRequest::factory()->count(1)->create(['status' => VerificationStatus::Verified]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.verifications.pending', 2)
            ->where('stats.verifications.verified', 1));
});

it('returns correct finance statistics', function (): void {
    Invoice::factory()->count(2)->create([
        'status' => InvoiceStatus::Paid,
        'amount' => 1000,
        'paid_at' => now(),
    ]);
    Invoice::factory()->create([
        'status' => InvoiceStatus::Unpaid,
        'amount' => 500,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.finance.totalRevenue', fn ($value) => (float) $value === 2000.0)
            ->where('stats.finance.monthlyRevenue', fn ($value) => (float) $value === 2000.0)
            ->where('stats.finance.unpaidInvoices', 1));
});

it('returns pending refund count', function (): void {
    RefundRequest::factory()->count(2)->create(['status' => RefundStatus::Pending]);
    RefundRequest::factory()->paid()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.finance.pendingRefunds', 2));
});

it('returns open contact message count', function (): void {
    ContactMessage::factory()->count(3)->create(['status' => ContactMessageStatus::Open]);
    ContactMessage::factory()->create(['status' => ContactMessageStatus::Closed]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.contactMessages.open', 3));
});

it('returns chart data with 12 monthly labels', function (): void {
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('charts.labels', 12)
            ->has('charts.newTutors', 12)
            ->has('charts.newGuardians', 12)
            ->has('charts.newJobs', 12)
            ->has('charts.revenue', 12));
});

it('returns recent jobs in activity section', function (): void {
    TuitionJob::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recentActivity.recentJobs', 3)
            ->has('recentActivity.recentJobs.0', fn ($job) => $job
                ->hasAll(['id', 'title', 'status', 'statusLabel', 'guardian', 'createdAt'])));
});

it('returns recent tickets in activity section', function (): void {
    SupportTicket::factory()->count(2)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recentActivity.recentTickets', 2)
            ->has('recentActivity.recentTickets.0', fn ($ticket) => $ticket
                ->hasAll(['id', 'ticketNumber', 'subject', 'status', 'statusLabel', 'priority', 'priorityLabel', 'user', 'createdAt'])));
});

it('returns pending verifications in activity section', function (): void {
    VerificationRequest::factory()->count(2)->create(['status' => VerificationStatus::Pending]);
    VerificationRequest::factory()->create(['status' => VerificationStatus::Verified]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recentActivity.pendingVerifications', 2)
            ->has('recentActivity.pendingVerifications.0', fn ($v) => $v
                ->hasAll(['id', 'userName', 'userRole', 'roleLabel', 'createdAt'])));
});

it('limits recent activity items to 5', function (): void {
    TuitionJob::factory()->count(8)->create();
    SupportTicket::factory()->count(7)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('recentActivity.recentJobs', 5)
            ->has('recentActivity.recentTickets', 5));
});

it('denies access to guests', function (): void {
    $this->get(route('admin.dashboard'))
        ->assertRedirect();
});

it('denies access to non-admin users', function (): void {
    $tutor = User::factory()->tutor()->create();

    $this->actingAs($tutor)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});
