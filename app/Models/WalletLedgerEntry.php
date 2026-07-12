<?php

namespace App\Models;

use App\Enums\LedgerEntryType;
use Database\Factories\WalletLedgerEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletLedgerEntry extends Model
{
    /** @use HasFactory<WalletLedgerEntryFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'journal_uuid',
        'owner_user_id',
        'type',
        'amount',
        'currency',
        'reference_type',
        'reference_id',
        'counterparty_user_id',
        'posted_at',
        'is_reversal',
        'reverses_journal_uuid',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => LedgerEntryType::class,
            'amount' => 'decimal:2',
            'posted_at' => 'datetime',
            'is_reversal' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * Get ledger owner.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Get counterparty owner.
     */
    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counterparty_user_id');
    }
}
