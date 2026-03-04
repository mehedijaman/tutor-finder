<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Enums\RefundStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\RefundRequestDecisionRequest;
use App\Http\Requests\Admin\Finance\RefundRequestMarkPaidRequest;
use App\Models\RefundRequest;
use App\Services\Finance\RefundWorkflowService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class RefundRequestController extends Controller
{
    /**
     * Display refund requests queue.
     */
    public function index(Request $request): Response
    {
        $status = strtolower(trim($request->string('status')->toString()));
        $search = trim($request->string('q')->toString());

        if ($status !== '' && RefundStatus::tryFrom($status) === null) {
            $status = '';
        }

        $items = RefundRequest::query()
            ->with([
                'assignment:id,job_id,tutor_user_id',
                'assignment.job:id,title,slug',
                'requester:id,name,email',
                'decisionBy:id,name',
                'payment:id,gateway,provider_txn_id',
            ])
            ->when($status !== '', fn (Builder $builder): Builder => $builder->where('status', $status))
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('reason_text', 'like', "%{$search}%")
                        ->orWhereHas('assignment.job', fn (Builder $jobQuery): Builder => $jobQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%"))
                        ->orWhereHas('requester', fn (Builder $requesterQuery): Builder => $requesterQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(20)
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
                'requester' => [
                    'id' => $refundRequest->requester?->id,
                    'name' => $refundRequest->requester?->name,
                    'email' => $refundRequest->requester?->email,
                ],
                'decision_by' => $refundRequest->decisionBy?->name,
                'payment' => $refundRequest->payment ? [
                    'gateway' => $refundRequest->payment->gateway,
                    'provider_txn_id' => $refundRequest->payment->provider_txn_id,
                ] : null,
            ]);

        return inertia('admin/finance/RefundRequests', [
            'items' => $items,
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
            'statusOptions' => RefundRequest::statuses(),
        ]);
    }

    /**
     * Approve or reject refund request.
     */
    public function decide(
        RefundRequestDecisionRequest $request,
        RefundRequest $refundRequest,
        RefundWorkflowService $refundWorkflowService,
    ): RedirectResponse {
        try {
            $status = $request->validated('status');
            $decisionNote = $request->validated('decision_note');

            if ($status === RefundStatus::Approved->value) {
                $refundWorkflowService->approve($refundRequest, $request->user(), $decisionNote);
            } else {
                $refundWorkflowService->reject(
                    refundRequest: $refundRequest,
                    admin: $request->user(),
                    decisionNote: (string) ($decisionNote ?: 'Rejected by admin.'),
                );
            }
        } catch (DomainException $exception) {
            return back()->withErrors([
                'refund' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'Refund request decision saved successfully.');
    }

    /**
     * Mark approved refund request as paid.
     */
    public function markPaid(
        RefundRequestMarkPaidRequest $request,
        RefundRequest $refundRequest,
        RefundWorkflowService $refundWorkflowService,
    ): RedirectResponse {
        try {
            $refundWorkflowService->markPaid(
                refundRequest: $refundRequest,
                admin: $request->user(),
                paymentContext: $request->validated(),
            );
        } catch (DomainException $exception) {
            return back()->withErrors([
                'refund' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'Refund payout recorded successfully.');
    }
}
