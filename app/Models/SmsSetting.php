<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'provider',
        'credentials',
        'is_default',
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
            'credentials' => 'encrypted:array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope query to active gateway settings.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Return supported provider class basenames from config/sms.php.
     *
     * @return array<int, string>
     */
    public static function availableProviders(): array
    {
        return collect(array_keys(config('sms.providers', [])))
            ->map(fn ($provider): string => class_basename((string) $provider))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
