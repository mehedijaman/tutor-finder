<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendTestEmailRequest;
use App\Http\Requests\Admin\SmtpSettingStoreRequest;
use App\Http\Requests\Admin\SmtpSettingUpdateRequest;
use App\Models\SmtpSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ViewErrorBag;
use Inertia\Response;
use Throwable;

class SmtpSettingController extends Controller
{
    /**
     * Display SMTP settings listing.
     */
    public function index(Request $request): Response
    {
        $sort = $request->string('sort')->toString();

        if (! in_array($sort, ['name', 'driver', 'updated_at', 'created_at'], true)) {
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

        $items = SmtpSetting::query()
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('driver', 'like', "%{$search}%")
                        ->orWhere('from_address', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->through(function (SmtpSetting $smtpSetting): array {
                $credentials = $smtpSetting->credentials;
                $credentialsMap = is_array($credentials) ? $credentials : [];
                $credentialKeys = array_keys($credentialsMap);
                $requiredKeys = SmtpSetting::requiredCredentialKeys($smtpSetting->driver);
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
                    'id' => $smtpSetting->id,
                    'name' => $smtpSetting->name,
                    'driver' => $smtpSetting->driver,
                    'from_address' => $smtpSetting->from_address,
                    'from_name' => $smtpSetting->from_name,
                    'is_default' => $smtpSetting->is_default,
                    'is_active' => $smtpSetting->is_active,
                    'credential_keys' => array_values($credentialKeys),
                    'configured_keys_count' => collect($credentialsMap)
                        ->filter(fn ($value): bool => trim((string) $value) !== '')
                        ->count(),
                    'required_keys_count' => count($requiredKeys),
                    'missing_required_keys' => $missingRequiredKeys,
                    'has_complete_credentials' => $missingRequiredKeys === [],
                    'updated_at' => $smtpSetting->updated_at?->toDateTimeString(),
                ];
            })
            ->withQueryString();

        return inertia('admin/smtp-settings/Index', [
            'items' => $items,
            'filters' => $filters,
            'permissions' => [
                'can_create' => $request->user()?->can('smtp-setting-create') ?? false,
                'can_test' => $request->user()?->can('smtp-setting-update') ?? false,
            ],
            'resultMessage' => session('status'),
            'errorMessage' => $this->resolveEmailError($request),
        ]);
    }

    /**
     * Show the create form for an SMTP setting.
     */
    public function create(): Response
    {
        return inertia('admin/smtp-settings/Create', [
            'drivers' => SmtpSetting::driverOptions(),
        ]);
    }

    /**
     * Store a newly created SMTP setting.
     */
    public function store(SmtpSettingStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->persist($validated);

        return redirect()->route('admin.smtp-settings.index')->with('status', 'SMTP setting created successfully.');
    }

    /**
     * Show the edit form for an SMTP setting.
     */
    public function edit(SmtpSetting $smtpSetting): Response
    {
        $credentials = $smtpSetting->credentials;
        $credentialsArray = is_array($credentials) ? $credentials : [];

        return inertia('admin/smtp-settings/Edit', [
            'smtpSetting' => [
                'id' => $smtpSetting->id,
                'name' => $smtpSetting->name,
                'driver' => $smtpSetting->driver,
                'from_address' => $smtpSetting->from_address,
                'from_name' => $smtpSetting->from_name,
                'is_default' => $smtpSetting->is_default,
                'is_active' => $smtpSetting->is_active,
                'credentials' => $credentialsArray,
            ],
            'drivers' => SmtpSetting::driverOptions(),
        ]);
    }

    /**
     * Update an existing SMTP setting.
     */
    public function update(SmtpSettingUpdateRequest $request, SmtpSetting $smtpSetting): RedirectResponse
    {
        $validated = $request->validated();

        $this->persist($validated, $smtpSetting);

        return redirect()->route('admin.smtp-settings.index')->with('status', 'SMTP setting updated successfully.');
    }

    /**
     * Delete an SMTP setting.
     */
    public function destroy(SmtpSetting $smtpSetting): RedirectResponse
    {
        $smtpSetting->delete();

        $this->ensureDefaultExists();

        return redirect()->route('admin.smtp-settings.index')->with('status', 'SMTP setting deleted successfully.');
    }

    /**
     * Send a test email through the default active SMTP setting.
     */
    public function testEmail(SendTestEmailRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $email = (string) $validated['email'];

        $smtpSetting = SmtpSetting::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if (! $smtpSetting instanceof SmtpSetting) {
            return redirect()
                ->route('admin.smtp-settings.index')
                ->withErrors([
                    'email' => 'No active default SMTP setting configured.',
                ]);
        }

        if (! $smtpSetting->hasCompleteCredentials()) {
            return redirect()
                ->route('admin.smtp-settings.index')
                ->withErrors([
                    'email' => 'The default SMTP setting is missing required credentials.',
                ]);
        }

        try {
            $this->applySmtpConfig($smtpSetting);

            Mail::raw('This is a test email from your application to verify SMTP settings are working correctly.', function (Message $message) use ($email, $smtpSetting): void {
                $message->to($email);
                $message->subject('Test Email - SMTP Configuration');

                if ($smtpSetting->from_address) {
                    $message->from($smtpSetting->from_address, $smtpSetting->from_name ?? config('app.name'));
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.smtp-settings.index')
                ->withErrors([
                    'email' => $exception->getMessage(),
                ]);
        }

        return redirect()
            ->route('admin.smtp-settings.index')
            ->with('status', 'Test email sent successfully to '.$email);
    }

    /**
     * Persist an SMTP setting and normalize default selection.
     *
     * @param  array<string, mixed>  $validated
     */
    protected function persist(array $validated, ?SmtpSetting $smtpSetting = null): void
    {
        DB::transaction(function () use ($validated, $smtpSetting): void {
            $isDefault = (bool) ($validated['is_default'] ?? false);
            $isActive = (bool) ($validated['is_active'] ?? false);

            $payload = [
                'name' => (string) $validated['name'],
                'driver' => (string) $validated['driver'],
                'from_address' => $validated['from_address'] ?? null,
                'from_name' => $validated['from_name'] ?? null,
                'credentials' => $validated['credentials'] ?? [],
                'is_default' => $isDefault,
                'is_active' => $isDefault ? true : $isActive,
            ];

            if ($isDefault) {
                $defaults = SmtpSetting::query();

                if ($smtpSetting instanceof SmtpSetting) {
                    $defaults->whereKeyNot($smtpSetting->getKey());
                }

                $defaults->where('is_default', true)->update(['is_default' => false]);
            }

            if ($smtpSetting instanceof SmtpSetting) {
                $smtpSetting->fill($payload)->save();
            } else {
                SmtpSetting::query()->create($payload);
            }

            $this->ensureDefaultExists();
        });
    }

    /**
     * Ensure at least one active setting remains default when available.
     */
    protected function ensureDefaultExists(): void
    {
        $activeDefaultExists = SmtpSetting::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->exists();

        if ($activeDefaultExists) {
            return;
        }

        $fallback = SmtpSetting::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->first();

        if (! $fallback) {
            return;
        }

        SmtpSetting::query()
            ->whereKeyNot($fallback->getKey())
            ->where('is_default', true)
            ->update(['is_default' => false]);

        if (! $fallback->is_default) {
            $fallback->forceFill(['is_default' => true])->save();
        }
    }

    /**
     * Apply SMTP configuration at runtime for sending test email.
     */
    protected function applySmtpConfig(SmtpSetting $smtpSetting): void
    {
        $mailConfig = $smtpSetting->toMailConfig();

        Config::set('mail.default', 'smtp_test');
        Config::set('mail.mailers.smtp_test', $mailConfig);

        if ($smtpSetting->from_address) {
            Config::set('mail.from.address', $smtpSetting->from_address);
            Config::set('mail.from.name', $smtpSetting->from_name ?? config('app.name'));
        }
    }

    /**
     * Resolve email-related error message from the session error bag.
     */
    private function resolveEmailError(Request $request): ?string
    {
        $errors = $request->session()->get('errors');

        if (! $errors instanceof ViewErrorBag) {
            return null;
        }

        if (! $errors->has('email')) {
            return null;
        }

        return $errors->first('email');
    }
}
