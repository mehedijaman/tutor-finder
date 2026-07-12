<?php

namespace App\Models;

use App\Enums\TaxonomyStatus;
use Database\Factories\BlogTagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogTag extends Model
{
    /** @use HasFactory<BlogTagFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
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
     * Get blog posts assigned to this tag.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            BlogPost::class,
            'blog_post_tag',
            'tag_id',
            'post_id',
        );
    }
}
