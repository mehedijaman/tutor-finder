<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TutorProfile extends Model
{
    /** @use HasFactory<\Database\Factories\TutorProfileFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'gender',
        'date_of_birth',
        'present_address',
        'permanent_address',
        'nid_no',
        'bio',
        'preferred_tuition_types',
        'preferred_categories',
        'preferred_classes',
        'preferred_subjects',
        'preferred_locations',
        'expected_salary_min',
        'expected_salary_max',
        'available_days',
        'available_time',
        'status',
    ];

    /**
     * Get casts for attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'preferred_tuition_types' => 'array',
            'preferred_categories' => 'array',
            'preferred_classes' => 'array',
            'preferred_subjects' => 'array',
            'preferred_locations' => 'array',
            'expected_salary_min' => 'decimal:2',
            'expected_salary_max' => 'decimal:2',
            'available_days' => 'array',
        ];
    }

    /**
     * Get tutor profile owner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get educations for this tutor profile via user relation.
     */
    public function educations(): HasMany
    {
        return $this->hasMany(TutorEducation::class, 'user_id', 'user_id');
    }
}
