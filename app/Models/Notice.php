<?php

namespace App\Models;

use App\Enums\NoticeAudience;
use App\Enums\UserRole;
use Database\Factories\NoticeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    /** @use HasFactory<NoticeFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'body',
        'audience',
        'expires_at',
        'published_at',
        'created_by_user_id',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'audience' => NoticeAudience::class,
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user who created the notice.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Scope to active notices (is_active=true and not expired).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope to notices for a specific audience role.
     */
    public function scopeForAudience(Builder $query, UserRole $role): Builder
    {
        $audienceValues = match ($role) {
            UserRole::Tutor => [NoticeAudience::Tutor->value, NoticeAudience::Both->value],
            UserRole::Guardian => [NoticeAudience::Guardian->value, NoticeAudience::Both->value],
            default => [],
        };

        return $query->whereIn('audience', $audienceValues);
    }

    /**
     * Check if the notice is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Check if the notice is visible for a given role.
     */
    public function isVisibleFor(UserRole $role): bool
    {
        if (! $this->is_active || $this->isExpired()) {
            return false;
        }

        return $this->audience->includesRole($role);
    }
}
