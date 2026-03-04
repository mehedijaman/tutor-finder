<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\RefundRequestStoreRequest;
use App\Models\RefundRequest;
use App\Models\TuitionJobAssignment;
use App\Services\Finance\RefundWorkflowService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class RefundRequestController extends Controller
{
    /**
     * Display tutor refund requests.
     */
    public function index(Request $request): Response
    {
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($status, RefundRequest::statuses(), true)) {
            $status = '';
        }

        $items = RefundRequest::query()
            ->with([
                'assignment:id,job_id,tutor_user_id',
                'assignment.job:id,title,slug',
                'decisionBy:id,name',
                'payment:id,gateway,provider_txn_id',
            ])
            ->where('requested_by_user_id', $request->user()?->getAuthIdentifier())
            ->when($status !== '', fn (Builder $builder): Builder => $builder->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (RefundRequest $refundRequest): array => [
                'id' => $refundRequest->id,
                'status' => $refundRequest->status,
                'reason_text' => $refundRequest->reason_text,
                'requested_at' => $refundRequest->requested_at?->toDateTimeString(),
                'amount' => $refundRequest->amount,
                'currency' => $refundRequest->currency,
                'decision_note' => $refundRequest->decision_note,
                'decided_at' => $refundRequest->decided_at?->toDateTimeString(),
                'paid_at' => $refundRequest->paid_at?->toDateTimeString(),
                'job' => [
                    'id' => $refundRequest->assignment?->job?->id,
                    'title' => $refundRequest->assignment?->job?->title,
                    'slug' => $refundRequest->assignment?->job?->slug,
                ],
                'decision_by' => $refundRequest->decisionBy?->name,
                'payment' => $refundRequest->payment ? [
                    'gateway' => $refundRequest->payment->gateway,
                    'provider_txn_id' => $refundRequest->payment->provider_txn_id,
                ] : null,
            ]);

        $eligibleAssignments = TuitionJobAssignment::query()
            ->with([
                'job:id,title,slug',
                'invoices' => fn (Builder $builder): Builder => $builder
                    ->select(['id', 'job_assignment_id', 'type', 'status', 'amount', 'currency'])
                    ->where('type', InvoiceType::PlatformServiceFee)
                    ->where('status', InvoiceStatus::Paid),
            ])
            ->where('tutor_user_id', $request->user()?->getAuthIdentifier())
            ->whereHas('invoices', fn (Builder $builder): Builder => $builder
                ->where('type', InvoiceType::PlatformServiceFee)
                ->where('status', InvoiceStatus::Paid))
            ->latest('id')
            ->get()
            ->map(fn (TuitionJobAssignment $assignment): array => [
                'id' => $assignment->id,
                'job_title' => $assignment->job?->title,
                'job_slug' => $assignment->job?->slug,
                'service_fee_amount' => $assignment->invoices->first()?->amount,
                'currency' => $assignment->invoices->first()?->currency,
            ])
            ->values()
            ->all();

        return inertia('tutor/finance/RefundRequests', [
            'items' => $items,
            'filters' => [
                'status' => $status,
            ],
            'statusOptions' => RefundRequest::statuses(),
            'eligibleAssignments' => $eligibleAssignments,
        ]);
    }

    /**
     * Store a tutor refund request for assignment.
     */
    public function store(
        RefundRequestStoreRequest $request,
        TuitionJobAssignment $assignment,
        RefundWorkflowService $refundWorkflowService,
    ): RedirectResponse {
        try {
            $refundWorkflowService->submit(
                assignment: $assignment,
                tutor: $request->user(),
                reasonText: (string) $request->validated('reason_text'),
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'refund' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'Refund request submitted successfully.');
    }
}
