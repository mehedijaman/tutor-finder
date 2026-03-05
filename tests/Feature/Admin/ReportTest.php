<?php

use App\Models\Invoice;
use App\Models\RefundRequest;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->admin()->create();
    $this->admin->assignRole('super-admin');
});

// ── Income Report ──────────────────────────────────────────────────────

it('renders the income report page', function (): void {
    Invoice::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.reports.income'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/IncomeReport')
            ->has('reportData')
            ->has('summary')
            ->has('filters')
            ->has('availableYears')
            ->has('typeOptions')
        );
});

it('exports income report as csv', function (): void {
    Invoice::factory()->count(2)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.reports.income.export', ['year' => now()->year]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertDownload();
});

it('filters income report by year and month', function (): void {
    $this->actingAs($this->admin)
        ->get(route('admin.reports.income', ['year' => now()->year, 'month' => now()->month]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/IncomeReport')
            ->where('filters.year', now()->year)
            ->where('filters.month', (string) now()->month)
        );
});

// ── Tuition Report ─────────────────────────────────────────────────────

it('renders the tuition report page', function (): void {
    TuitionJob::factory()->count(2)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.reports.tuition'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/TuitionReport')
            ->has('reportData')
            ->has('summary')
            ->has('filters')
            ->has('availableYears')
        );
});

it('exports tuition report as csv', function (): void {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.reports.tuition.export', ['year' => now()->year]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertDownload();
});

// ── Refund Report ──────────────────────────────────────────────────────

it('renders the refund report page', function (): void {
    RefundRequest::factory()->count(2)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.reports.refunds'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/RefundReport')
            ->has('reportData')
            ->has('summary')
            ->has('filters')
            ->has('availableYears')
            ->has('statusOptions')
        );
});

it('exports refund report as csv', function (): void {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.reports.refunds.export', ['year' => now()->year]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertDownload();
});

// ── User Registration Report ───────────────────────────────────────────

it('renders the user registration report page', function (): void {
    User::factory()->tutor()->count(3)->create();
    User::factory()->guardian()->count(2)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.reports.user-registrations'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/UserRegistrationReport')
            ->has('reportData')
            ->has('summary')
            ->has('filters')
            ->has('availableYears')
        );
});

it('exports user registration report as csv', function (): void {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.reports.user-registrations.export', ['year' => now()->year]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertDownload();
});

it('filters user registration report by role', function (): void {
    $this->actingAs($this->admin)
        ->get(route('admin.reports.user-registrations', ['year' => now()->year, 'role' => 'tutor']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/UserRegistrationReport')
            ->where('filters.role', 'tutor')
        );
});

// ── Job Performance Report ─────────────────────────────────────────────

it('renders the job performance report page', function (): void {
    TuitionJobApplication::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.reports.job-performance'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/reports/JobPerformanceReport')
            ->has('reportData')
            ->has('summary')
            ->has('filters')
            ->has('availableYears')
        );
});

it('exports job performance report as csv', function (): void {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.reports.job-performance.export', ['year' => now()->year]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertDownload();
});

// ── Authorization ──────────────────────────────────────────────────────

it('denies report access to guests', function (): void {
    $this->get(route('admin.reports.income'))->assertRedirect();
});

it('denies report access to non-admin users', function (): void {
    $tutor = User::factory()->tutor()->create();

    $this->actingAs($tutor)
        ->get(route('admin.reports.income'))
        ->assertForbidden();
});

it('denies report access to admin without report-view permission', function (): void {
    $limitedAdmin = User::factory()->admin()->create();

    $this->actingAs($limitedAdmin)
        ->get(route('admin.reports.income'))
        ->assertForbidden();
});
