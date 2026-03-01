<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingUpdateRequest;
use App\Models\SiteSetting;
use App\Support\SiteSettingsResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Response;

class SiteSettingController extends Controller
{
    /**
     * Show the site settings edit page.
     */
    public function edit(SiteSettingsResolver $siteSettingsResolver): Response
    {
        return inertia('admin/site-settings/Edit', [
            'siteSettingsFull' => $siteSettingsResolver->full(),
        ]);
    }

    /**
     * Update the singleton site settings.
     */
    public function update(
        SiteSettingUpdateRequest $request,
        SiteSettingsResolver $siteSettingsResolver,
    ): RedirectResponse {
        $validated = $request->validated();
        $siteSetting = SiteSetting::current();

        $logoPath = $this->handleLogoUpload(
            $request->file('logo'),
            $request->boolean('remove_logo'),
            $siteSetting->logo_path,
        );

        $siteSetting->fill([
            'site_name' => trim((string) $validated['site_name']),
            'slogan' => $this->nullableString($validated['slogan'] ?? null),
            'description' => $this->nullableString($validated['description'] ?? null),
            'logo_path' => $logoPath,
            'phone_numbers' => $this->normalizeStringList($validated['phone_numbers'] ?? []),
            'emails' => $this->normalizeStringList($validated['emails'] ?? [], true),
            'addresses' => $this->normalizeAddresses($validated['addresses'] ?? []),
            'social_details' => $this->normalizeSocialDetails($validated['social_details'] ?? []),
            'trade_licence_no' => $this->nullableString($validated['trade_licence_no'] ?? null),
            'tin_no' => $this->nullableString($validated['tin_no'] ?? null),
            'bin_no' => $this->nullableString($validated['bin_no'] ?? null),
        ])->save();

        $siteSettingsResolver->flush();

        return redirect()
            ->route('admin.site-settings.edit')
            ->with('status', 'Site settings updated successfully.');
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeStringList(array $values, bool $lowercase = false): array
    {
        return collect($values)
            ->map(function ($value) use ($lowercase): string {
                $normalized = trim((string) $value);

                if ($lowercase) {
                    $normalized = Str::lower($normalized);
                }

                return $normalized;
            })
            ->filter(fn (string $value): bool => $value !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $addresses
     * @return array<int, array{label: string|null, address: string, map_url: string|null}>
     */
    protected function normalizeAddresses(array $addresses): array
    {
        return collect($addresses)
            ->map(function ($address): ?array {
                if (! is_array($address)) {
                    return null;
                }

                $label = trim((string) ($address['label'] ?? ''));
                $line = trim((string) ($address['address'] ?? ''));
                $mapUrl = trim((string) ($address['map_url'] ?? ''));

                if ($line === '') {
                    return null;
                }

                return [
                    'label' => $label !== '' ? $label : null,
                    'address' => $line,
                    'map_url' => $mapUrl !== '' ? $mapUrl : null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $socialDetails
     * @return array<string, string>
     */
    protected function normalizeSocialDetails(array $socialDetails): array
    {
        return collect($socialDetails)
            ->reduce(function (array $carry, $item): array {
                if (! is_array($item)) {
                    return $carry;
                }

                $platform = trim((string) ($item['platform'] ?? ''));
                $url = trim((string) ($item['url'] ?? ''));
                $platformKey = Str::slug(Str::lower($platform), '_');

                if ($platformKey === '' || $url === '') {
                    return $carry;
                }

                $carry[$platformKey] = $url;

                return $carry;
            }, []);
    }

    protected function handleLogoUpload(
        ?UploadedFile $logoFile,
        bool $removeLogo,
        mixed $existingPath,
    ): ?string {
        $logoPath = is_string($existingPath) && trim($existingPath) !== '' ? $existingPath : null;

        if ($removeLogo && $logoPath !== null) {
            Storage::disk('public')->delete($logoPath);
            $logoPath = null;
        }

        if ($logoFile instanceof UploadedFile) {
            if ($logoPath !== null) {
                Storage::disk('public')->delete($logoPath);
            }

            $logoPath = $logoFile->store('site-settings', 'public');
        }

        return $logoPath;
    }

    protected function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
