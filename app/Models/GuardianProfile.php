<?php

namespace App\Models;

use App\Enums\TaxonomyStatus;
use Database\Factories\GuardianProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuardianProfile extends Model
{
    /** @use HasFactory<GuardianProfileFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'phone_alt',
        'emergency_contact',
        'guardian_name',
        'relationship_to_student',
        'address',
        'city',
        'area',
        'occupation',
        'notes',
        'admin_notes',
        'preferred_contact_time',
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
