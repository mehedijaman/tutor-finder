<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\WalletLedgerEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Response;

class LedgerController extends Controller
{
    /**
     * Display platform ledger entries.
     */
    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $referenceType = strtolower(trim($request->string('reference_type')->toString()));

        if (! in_array($referenceType, ['invoice', 'refund_request'], true)) {
            $referenceType = '';
        }

        $items = WalletLedgerEntry::query()
            ->with([
                'owner:id,name,email,role',
                'counterparty:id,name,email,role',
            ])
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('journal_uuid', 'like', "%{$search}%")
                        ->orWhere('reference_id', (int) $search)
                        ->orWhereHas('owner', fn (Builder $ownerQuery): Builder => $ownerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($referenceType !== '', fn (Builder $builder): Builder => $builder->where('reference_type', $referenceType))
            ->latest('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (WalletLedgerEntry $entry): array => [
                'id' => $entry->id,
                'journal_uuid' => $entry->journal_uuid,
                'type' => $entry->type,
                'amount' => $entry->amount,
                'currency' => $entry->currency,
                'reference_type' => $entry->reference_type,
                'reference_id' => $entry->reference_id,
                'posted_at' => $entry->posted_at?->toDateTimeString(),
                'is_reversal' => $entry->is_reversal,
                'reverses_journal_uuid' => $entry->reverses_journal_uuid,
                'owner' => [
                    'id' => $entry->owner?->id,
                    'name' => $entry->owner?->name,
                    'email' => $entry->owner?->email,
                ],
                'counterparty' => [
                    'id' => $entry->counterparty?->id,
                    'name' => $entry->counterparty?->name,
                    'email' => $entry->counterparty?->email,
                ],
            ]);

        return inertia('admin/finance/Ledger', [
            'items' => $items,
            'filters' => [
                'q' => $search,
                'reference_type' => $referenceType,
            ],
            'referenceTypeOptions' => ['invoice', 'refund_request'],
        ]);
    }
}
