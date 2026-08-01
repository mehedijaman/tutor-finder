<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\ApplicationStatus;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Notice;
use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use Illuminate\Http\Request;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the tutor dashboard.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        $applicationCounts = TuitionJobApplication::query()
            ->where('tutor_user_id', $userId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $notices = Notice::query()
            ->active()
            ->forAudience(UserRole::Tutor)
            ->orderByDesc('published_at')
            ->limit(10)
            ->get(['id', 'title', 'body', 'published_at', 'expires_at']);

        $verificationFeeInvoice = Invoice::query()
            ->where('payer_user_id', $userId)
            ->where('type', InvoiceType::TutorVerificationFee)
            ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Void, InvoiceStatus::Refunded])
            ->latest('id')
            ->first(['id', 'invoice_no', 'status', 'amount', 'currency', 'due_at']);

        $pendingInvoiceCount = Invoice::query()
            ->where('payer_user_id', $userId)
            ->whereIn('status', [InvoiceStatus::Unpaid, InvoiceStatus::Processing])
            ->count();

        $tutorProfile = $user?->tutorProfile;
        $tutorCityId = $tutorProfile?->city_id;

        $nearbyJobsCount = TuitionJob::query()
            ->active()
            ->when($tutorCityId, fn ($q) => $q->where('city_id', $tutorCityId))
            ->count();

        return inertia('tutor/Dashboard', [
            'notices' => $notices,
            'nearbyJobsCount' => $nearbyJobsCount,
            'applicationStats' => [
                'applied' => (int) ($applicationCounts[ApplicationStatus::Applied->value] ?? 0),
                'shortlisted' => (int) ($applicationCounts[ApplicationStatus::Shortlisted->value] ?? 0),
                'appointed' => (int) ($applicationCounts[ApplicationStatus::Appointed->value] ?? 0),
                'confirmed' => (int) ($applicationCounts[ApplicationStatus::Confirmed->value] ?? 0),
                'cancelled' => (int) ($applicationCounts[ApplicationStatus::Cancelled->value] ?? 0),
            ],
            'verificationStatus' => $user?->verification_status?->value ?? VerificationStatus::Unverified->value,
            'isVerified' => $user?->verification_status === VerificationStatus::Verified,
            'verificationFeeInvoice' => $verificationFeeInvoice ? [
                'id' => $verificationFeeInvoice->id,
                'invoice_no' => $verificationFeeInvoice->invoice_no,
                'status' => $verificationFeeInvoice->status,
                'amount' => $verificationFeeInvoice->amount,
                'currency' => $verificationFeeInvoice->currency,
                'due_at' => $verificationFeeInvoice->due_at?->toDateTimeString(),
            ] : null,
            'pendingInvoiceCount' => $pendingInvoiceCount,
        ]);
    }
}
