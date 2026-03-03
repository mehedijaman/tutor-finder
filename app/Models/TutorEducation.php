<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TutorEducation extends Model
{
    /** @use HasFactory<\Database\Factories\TutorEducationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'tutor_educations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'degree',
        'institute',
        'department',
        'graduation_year',
        'result',
        'is_current',
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
            'graduation_year' => 'integer',
            'is_current' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get tutor user of education record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
