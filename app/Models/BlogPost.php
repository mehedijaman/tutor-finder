<?php

namespace App\Models;

use Database\Factories\BlogPostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BlogPost extends Model implements HasMedia
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'status',
        'published_at',
        'author_admin_id',
        'meta_title',
        'meta_description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Get categories attached to this post.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            BlogCategory::class,
            'blog_category_post',
            'post_id',
            'category_id',
        );
    }

    /**
     * Get tags attached to this post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            BlogTag::class,
            'blog_post_tag',
            'post_id',
            'tag_id',
        );
    }

    /**
     * Get the post author.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_admin_id');
    }

    /**
     * Register post media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }
}
