<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteSettingsResolver
{
    /**
     * Resolve minimal site settings for globally shared props.
     *
     * @return array<string, mixed>
     */
    public function publicPayload(): array
    {
        return Cache::remember(
            SiteSetting::PUBLIC_CACHE_KEY,
            now()->addMinutes(60),
            fn (): array => SiteSetting::current()->toPublicPayload(),
        );
    }

    /**
     * Resolve full site settings for the admin editor.
     *
     * @return array<string, mixed>
     */
    public function full(): array
    {
        return SiteSetting::current()->toAdminPayload();
    }

    /**
     * Clear all site settings cache entries.
     */
    public function flush(): void
    {
        Cache::forget(SiteSetting::PUBLIC_CACHE_KEY);
    }
}
