<?php

namespace App\Models;

use App\Enums\PageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Page extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use HasFactory, InteractsWithMedia;

    use SoftDeletes;

    /**
     * System page slugs that cannot be deleted.
     *
     * @var list<string>
     */
    public const array SYSTEM_SLUGS = [
        'privacy-policy',
        'terms-of-service',
        'about-us',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'is_system',
        'meta_title',
        'meta_description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'is_system' => 'boolean',
        ];
    }

    /**
     * Determine if this page is a system page.
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')->singleFile();
    }
}
