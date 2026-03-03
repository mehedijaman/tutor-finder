<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuardianProfile extends Model
{
    /** @use HasFactory<\Database\Factories\GuardianProfileFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'phone_alt',
        'guardian_name',
        'address',
        'occupation',
        'notes',
        'status',
    ];

    /**
     * Get guardian profile owner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
