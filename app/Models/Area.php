<?php

namespace App\Models;

use App\Enums\TaxonomyStatus;
use Database\Factories\AreaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    /** @use HasFactory<AreaFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'city_id',
        'name',
        'slug',
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
     * Get the city this area belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get jobs in this area.
     */
    public function tuitionJobs(): HasMany
    {
        return $this->hasMany(TuitionJob::class);
    }

    /**
     * Apply default ordering.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }
}
