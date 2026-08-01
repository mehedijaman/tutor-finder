<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\JobStatus;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Notice;
use App\Models\TuitionJob;
use Illuminate\Http\Request;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the guardian dashboard.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        $jobCounts = TuitionJob::query()
            ->where('guardian_id', $userId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $notices = Notice::query()
            ->active()
            ->forAudience(UserRole::Guardian)
            ->orderByDesc('published_at')
            ->limit(10)
            ->get(['id', 'title', 'body', 'published_at', 'expires_at']);

        $verificationFeeInvoice = Invoice::query()
            ->where('payer_user_id', $userId)
            ->where('type', InvoiceType::GuardianVerificationFee)
            ->whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Void, InvoiceStatus::Refunded])
            ->latest('id')
            ->first(['id', 'invoice_no', 'status', 'amount', 'currency', 'due_at']);

        $pendingInvoiceCount = Invoice::query()
            ->where('payer_user_id', $userId)
            ->whereIn('status', [InvoiceStatus::Unpaid, InvoiceStatus::Processing])
            ->count();

        return inertia('guardian/Dashboard', [
            'notices' => $notices,
            'jobStats' => [
                'pending' => (int) ($jobCounts[JobStatus::Pending->value] ?? 0),
                'live' => (int) ($jobCounts[JobStatus::Live->value] ?? 0),
                'confirmed' => (int) ($jobCounts[JobStatus::Confirmed->value] ?? 0),
                'cancelled' => (int) ($jobCounts[JobStatus::Cancelled->value] ?? 0),
                'closed' => (int) ($jobCounts[JobStatus::Closed->value] ?? 0),
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
