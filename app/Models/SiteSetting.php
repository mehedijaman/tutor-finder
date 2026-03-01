<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteSetting extends Model
{
    /** @use HasFactory<\Database\Factories\SiteSettingFactory> */
    use HasFactory;

    public const PUBLIC_CACHE_KEY = 'site_settings.public';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'site_name',
        'slogan',
        'description',
        'logo_path',
        'phone_numbers',
        'emails',
        'addresses',
        'social_details',
        'trade_licence_no',
        'tin_no',
        'bin_no',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phone_numbers' => 'array',
            'emails' => 'array',
            'addresses' => 'array',
            'social_details' => 'array',
        ];
    }

    /**
     * Retrieve the singleton site setting row.
     */
    public static function current(): self
    {
        $siteSetting = static::query()->find(1);

        if ($siteSetting instanceof self) {
            return $siteSetting;
        }

        return static::query()->updateOrCreate(
            ['id' => 1],
            [
                'site_name' => (string) config('app.name', 'Tutor Finder'),
                'phone_numbers' => [],
                'emails' => [],
                'addresses' => [],
                'social_details' => [],
            ],
        );
    }

    /**
     * Serialize minimal data for globally shared public props.
     *
     * @return array<string, mixed>
     */
    public function toPublicPayload(): array
    {
        $siteName = is_string($this->site_name) && trim($this->site_name) !== ''
            ? trim($this->site_name)
            : (string) config('app.name', 'Tutor Finder');
        $phoneNumbers = $this->normalizeStringList($this->phone_numbers);
        $emails = $this->normalizeStringList($this->emails);
        $addresses = $this->normalizeAddresses($this->addresses);

        return [
            'site_name' => $siteName,
            'logo_url' => $this->logoUrl(),
            'primary_phone' => $phoneNumbers[0] ?? null,
            'primary_email' => $emails[0] ?? null,
            'primary_address' => $addresses[0]['address'] ?? null,
            'social_details' => $this->normalizeSocialDetails($this->social_details),
        ];
    }

    /**
     * Serialize complete data for admin settings editor.
     *
     * @return array<string, mixed>
     */
    public function toAdminPayload(): array
    {
        $siteName = is_string($this->site_name) && trim($this->site_name) !== ''
            ? trim($this->site_name)
            : (string) config('app.name', 'Tutor Finder');

        return [
            'site_name' => $siteName,
            'slogan' => $this->slogan,
            'description' => $this->description,
            'logo_url' => $this->logoUrl(),
            'phone_numbers' => $this->normalizeStringList($this->phone_numbers),
            'emails' => $this->normalizeStringList($this->emails),
            'addresses' => $this->normalizeAddresses($this->addresses),
            'social_details' => $this->normalizeSocialDetails($this->social_details),
            'trade_licence_no' => $this->trade_licence_no,
            'tin_no' => $this->tin_no,
            'bin_no' => $this->bin_no,
        ];
    }

    /**
     * Resolve the public URL for the configured logo path.
     */
    protected function logoUrl(): ?string
    {
        if (! is_string($this->logo_path) || trim($this->logo_path) === '') {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    /**
     * Normalize a list of string values.
     *
     * @return array<int, string>
     */
    protected function normalizeStringList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return collect($values)
            ->map(fn ($value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->values()
            ->all();
    }

    /**
     * Normalize addresses for editor/public usage.
     *
     * @return array<int, array{label: string|null, address: string, map_url: string|null}>
     */
    protected function normalizeAddresses(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return collect($values)
            ->map(function ($item): ?array {
                if (! is_array($item)) {
                    return null;
                }

                $label = trim((string) ($item['label'] ?? ''));
                $address = trim((string) ($item['address'] ?? ''));
                $mapUrl = trim((string) ($item['map_url'] ?? ''));

                if ($address === '') {
                    return null;
                }

                return [
                    'label' => $label !== '' ? $label : null,
                    'address' => $address,
                    'map_url' => $mapUrl !== '' ? $mapUrl : null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Normalize social details to a platform=>url map.
     *
     * @return array<string, string>
     */
    protected function normalizeSocialDetails(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        $normalized = [];
        $isAssociative = ! array_is_list($values);

        if ($isAssociative) {
            foreach ($values as $platform => $url) {
                $platformKey = Str::slug(Str::lower(trim((string) $platform)), '_');
                $socialUrl = trim((string) $url);

                if ($platformKey === '' || $socialUrl === '') {
                    continue;
                }

                $normalized[$platformKey] = $socialUrl;
            }

            return $normalized;
        }

        foreach ($values as $item) {
            if (! is_array($item)) {
                continue;
            }

            $platformKey = Str::slug(Str::lower(trim((string) ($item['platform'] ?? ''))), '_');
            $socialUrl = trim((string) ($item['url'] ?? ''));

            if ($platformKey === '' || $socialUrl === '') {
                continue;
            }

            $normalized[$platformKey] = $socialUrl;
        }

        return $normalized;
    }
}
