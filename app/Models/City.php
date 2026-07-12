<?php

namespace App\Models;

use App\Enums\TaxonomyStatus;
use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'country_id',
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
     * Get the country this city belongs to.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get all areas in this city.
     */
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    /**
     * Get jobs in this city.
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
