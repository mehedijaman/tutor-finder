<?php

namespace App\Models;

use App\Enums\TaxonomyStatus;
use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'class_id',
        'name',
        'slug',
        'status',
        'sort_order',
    ];

    /**
     * Get casts for attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaxonomyStatus::class,
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get class this subject belongs to.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get jobs linked with this subject.
     */
    public function tuitionJobs(): BelongsToMany
    {
        return $this->belongsToMany(TuitionJob::class, 'tuition_job_subjects', 'subject_id', 'job_id')
            ->withTimestamps();
    }

    /**
     * Apply default ordering.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
