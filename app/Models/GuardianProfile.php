<?php

namespace App\Models;

use App\Enums\TaxonomyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuardianProfile extends Model
{
    /** @use HasFactory<\Database\Factories\GuardianProfileFactory> */
    use HasFactory, SoftDeletes;

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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaxonomyStatus::class,
        ];
    }

    /**
     * Get guardian profile owner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
