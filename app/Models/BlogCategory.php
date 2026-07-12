<?php

namespace App\Models;

use App\Enums\TaxonomyStatus;
use Database\Factories\BlogCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BlogCategory extends Model implements HasMedia
{
    /** @use HasFactory<BlogCategoryFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'meta_title',
        'meta_description',
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
     * Get blog posts that belong to this category.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            BlogPost::class,
            'blog_category_post',
            'category_id',
            'post_id',
        );
    }

    /**
     * Register category media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }
}
