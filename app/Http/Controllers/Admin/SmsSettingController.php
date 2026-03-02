<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\SmsSender;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendTestSmsRequest;
use App\Http\Requests\Admin\SmsSettingStoreRequest;
use App\Http\Requests\Admin\SmsSettingUpdateRequest;
use App\Models\SmsSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Inertia\Response;
use Throwable;

class SmsSettingController extends Controller
{
    /**
     * Display SMS gateway settings.
     */
    public function index(Request $request): Response
    {
        $sort = $request->string('sort')->toString();

        if (! in_array($sort, ['name', 'provider', 'updated_at', 'created_at'], true)) {
            $sort = 'updated_at';
        }

        $direction = strtolower($request->string('direction')->toString());

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $filters = [
            'search' => trim($request->string('search')->toString()),
            'sort' => $sort,
            'direction' => $direction,
        ];

        $items = SmsSetting::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('provider', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->through(function (SmsSetting $smsSetting): array {
                $credentials = $smsSetting->credentials;
                $credentialsMap = is_array($credentials) ? $credentials : [];
                $credentialKeys = array_keys($credentialsMap);
                $requiredKeys = SmsSetting::requiredCredentialKeys($smsSetting->provider);
                $missingRequiredKeys = collect($requiredKeys)
                    ->filter(function (string $key) use ($credentialsMap): bool {
                        if (! array_key_exists($key, $credentialsMap)) {
                            return true;
                        }

                        return trim((string) $credentialsMap[$key]) === '';
                    })
                    ->values()
                    ->all();

                return [
                    'id' => $smsSetting->id,
                    'name' => $smsSetting->name,
                    'provider' => $smsSetting->provider,
                    'is_default' => $smsSetting->is_default,
                    'is_active' => $smsSetting->is_active,
                    'credential_keys' => array_values($credentialKeys),
                    'configured_keys_count' => collect($credentialsMap)
                        ->filter(fn ($value): bool => trim((string) $value) !== '')
                        ->count(),
                    'required_keys_count' => count($requiredKeys),
                    'missing_required_keys' => $missingRequiredKeys,
                    'has_complete_credentials' => $missingRequiredKeys === [],
                    'updated_at' => $smsSetting->updated_at?->toDateTimeString(),
                ];
            })
            ->withQueryString();

        return inertia('admin/sms-settings/Index', [
            'items' => $items,
            'filters' => $filters,
            'permissions' => [
                'can_create' => $request->user()?->can('sms-setting-create') ?? false,
                'can_test' => $request->user()?->can('sms-setting-update') ?? false,
            ],
            'resultMessage' => session('status'),
            'errorMessage' => $this->resolveSmsError($request),
        ]);
    }

    /**
     * Show the create form for an SMS gateway setting.
     */
    public function create(): Response
    {
        return inertia('admin/sms-settings/Create', [
            'providers' => SmsSetting::providerOptions(),
        ]);
    }

    /**
     * Store a newly created SMS gateway setting.
     */
    public function store(SmsSettingStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->persist($validated);

        return redirect()->route('admin.sms-settings.index')->with('status', 'SMS setting created successfully.');
    }

    /**
     * Show the edit form for an SMS gateway setting.
     */
    public function edit(SmsSetting $smsSetting): Response
    {
        $credentials = $smsSetting->credentials;
        $credentialsArray = is_array($credentials) ? $credentials : [];

        return inertia('admin/sms-settings/Edit', [
            'smsSetting' => [
                'id' => $smsSetting->id,
                'name' => $smsSetting->name,
                'provider' => $smsSetting->provider,
                'is_default' => $smsSetting->is_default,
                'is_active' => $smsSetting->is_active,
                'credentials' => $credentialsArray,
            ],
            'providers' => SmsSetting::providerOptions(),
        ]);
    }

    /**
     * Update an existing SMS gateway setting.
     */
    public function update(SmsSettingUpdateRequest $request, SmsSetting $smsSetting): RedirectResponse
    {
        $validated = $request->validated();

        $this->persist($validated, $smsSetting);

        return redirect()->route('admin.sms-settings.index')->with('status', 'SMS setting updated successfully.');
    }

    /**
     * Send a test SMS through the default active gateway setting.
     */
    public function testSms(SendTestSmsRequest $request, SmsSender $smsSender): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $smsSender->send(
                (string) $validated['mobile'],
                (string) $validated['message'],
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.sms-settings.index')
                ->withErrors([
                    'sms' => $exception->getMessage(),
                ]);
        }

        return redirect()
            ->route('admin.sms-settings.index')
            ->with('status', 'Test SMS sent successfully.');
    }

    /**
     * Persist an SMS gateway setting and normalize default selection.
     *
     * @param  array<string, mixed>  $validated
     */
    protected function persist(array $validated, ?SmsSetting $smsSetting = null): void
    {
        DB::transaction(function () use ($validated, $smsSetting): void {
            $isDefault = (bool) ($validated['is_default'] ?? false);
            $isActive = (bool) ($validated['is_active'] ?? false);

            $payload = [
                'name' => (string) $validated['name'],
                'provider' => (string) $validated['provider'],
                'credentials' => $validated['credentials'],
                'is_default' => $isDefault,
                'is_active' => $isDefault ? true : $isActive,
            ];

            if ($isDefault) {
                $defaults = SmsSetting::query();

                if ($smsSetting instanceof SmsSetting) {
                    $defaults->whereKeyNot($smsSetting->getKey());
                }

                $defaults->where('is_default', true)->update(['is_default' => false]);
            }

            if ($smsSetting instanceof SmsSetting) {
                $smsSetting->fill($payload)->save();
            } else {
                $smsSetting = SmsSetting::query()->create($payload);
            }

            $this->ensureDefaultExists();
        });
    }

    /**
     * Ensure at least one active setting remains default when available.
     */
    protected function ensureDefaultExists(): void
    {
        $activeDefaultExists = SmsSetting::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->exists();

        if ($activeDefaultExists) {
            return;
        }

        $fallback = SmsSetting::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->first();

        if (! $fallback) {
            return;
        }

        SmsSetting::query()
            ->whereKeyNot($fallback->getKey())
            ->where('is_default', true)
            ->update(['is_default' => false]);

        if (! $fallback->is_default) {
            $fallback->forceFill(['is_default' => true])->save();
        }
    }

    /**
     * Resolve SMS-related error message from the session error bag.
     */
    private function resolveSmsError(Request $request): ?string
    {
        $errors = $request->session()->get('errors');

        if (! $errors instanceof ViewErrorBag) {
            return null;
        }

        if (! $errors->has('sms')) {
            return null;
        }

        return $errors->first('sms');
    }
}
