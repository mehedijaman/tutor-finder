<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\SupportTicketMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SupportTicketMessage extends Model implements HasMedia
{
    /** @use HasFactory<SupportTicketMessageFactory> */
    use HasFactory, InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'body',
    ];

    /**
     * Register media collections for message attachments.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif']);
    }

    /**
     * Get the ticket this message belongs to.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    /**
     * Get the user who wrote this message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if this message was posted by an admin.
     */
    public function isAdminReply(): bool
    {
        return $this->user?->role === UserRole::Admin;
    }
}
