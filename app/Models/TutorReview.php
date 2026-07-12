<?php

namespace App\Models;

use Database\Factories\TutorReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TutorReview extends Model
{
    /** @use HasFactory<TutorReviewFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tutor_user_id',
        'guardian_user_id',
        'job_assignment_id',
        'rating',
        'comment',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    /**
     * Get the tutor being reviewed.
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_user_id');
    }

    /**
     * Get the guardian who wrote the review.
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_user_id');
    }

    /**
     * Get the job assignment associated with this review.
     */
    public function jobAssignment(): BelongsTo
    {
        return $this->belongsTo(TuitionJobAssignment::class, 'job_assignment_id');
    }
}
