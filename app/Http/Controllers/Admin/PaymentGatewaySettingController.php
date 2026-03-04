<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentGatewayType;
use App\Enums\TaxonomyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentGatewaySettingUpdateRequest;
use App\Models\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class PaymentGatewaySettingController extends Controller
{
    /**
     * Show payment gateway settings page.
     */
    public function edit(): Response
    {
        PaymentGateway::ensureDefaults();

        $gateways = PaymentGateway::query()
            ->whereIn('gateway', [
                PaymentGatewayType::Bkash->value,
                PaymentGatewayType::Sslcommerz->value,
                PaymentGatewayType::Manual->value,
            ])
            ->get()
            ->keyBy('gateway');

        $bkash = $gateways->get(PaymentGatewayType::Bkash->value);
        $sslCommerz = $gateways->get(PaymentGatewayType::Sslcommerz->value);
        $manual = $gateways->get(PaymentGatewayType::Manual->value);

        return inertia('admin/payment-settings/Edit', [
            'paymentSettings' => [
                'bkash' => [
                    'status' => $bkash?->status ?? TaxonomyStatus::Active->value,
                    'app_key' => (string) ($bkash?->credentials['app_key'] ?? ''),
                    'username' => (string) ($bkash?->credentials['username'] ?? ''),
                    'base_url' => (string) ($bkash?->credentials['base_url'] ?? ''),
                    'has_app_secret' => $this->hasCredential($bkash?->credentials['app_secret'] ?? null),
                    'has_password' => $this->hasCredential($bkash?->credentials['password'] ?? null),
                ],
                'sslcommerz' => [
                    'status' => $sslCommerz?->status ?? TaxonomyStatus::Active->value,
                    'store_id' => (string) ($sslCommerz?->credentials['store_id'] ?? ''),
                    'mode' => (string) ($sslCommerz?->credentials['mode'] ?? 'sandbox'),
                    'has_store_password' => $this->hasCredential($sslCommerz?->credentials['store_password'] ?? null),
                ],
                'manual' => [
                    'status' => $manual?->status ?? TaxonomyStatus::Active->value,
                    'notes' => (string) ($manual?->notes ?? 'Manual payment requires admin approval.'),
                ],
            ],
        ]);
    }

    /**
     * Update payment gateway settings.
     */
    public function update(PaymentGatewaySettingUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $bkash = PaymentGateway::query()->withTrashed()->firstOrNew([
                'gateway' => PaymentGatewayType::Bkash->value,
            ]);
            $sslCommerz = PaymentGateway::query()->withTrashed()->firstOrNew([
                'gateway' => PaymentGatewayType::Sslcommerz->value,
            ]);
            $manual = PaymentGateway::query()->withTrashed()->firstOrNew([
                'gateway' => PaymentGatewayType::Manual->value,
            ]);

            if ($bkash->trashed()) {
                $bkash->restore();
            }

            if ($sslCommerz->trashed()) {
                $sslCommerz->restore();
            }

            if ($manual->trashed()) {
                $manual->restore();
            }

            $bkashCredentials = is_array($bkash->credentials) ? $bkash->credentials : [];
            $sslCommerzCredentials = is_array($sslCommerz->credentials) ? $sslCommerz->credentials : [];

            $bkash->fill([
                'name' => 'bKash',
                'status' => $validated['bkash']['status'],
                'credentials' => [
                    'app_key' => $validated['bkash']['app_key'] ?? null,
                    'app_secret' => $this->resolveSecret(
                        $validated['bkash']['app_secret'] ?? null,
                        $bkashCredentials['app_secret'] ?? null,
                    ),
                    'username' => $validated['bkash']['username'] ?? null,
                    'password' => $this->resolveSecret(
                        $validated['bkash']['password'] ?? null,
                        $bkashCredentials['password'] ?? null,
                    ),
                    'base_url' => $validated['bkash']['base_url'] ?? null,
                ],
                'notes' => null,
            ])->save();

            $sslCommerz->fill([
                'name' => 'SSLCommerz',
                'status' => $validated['sslcommerz']['status'],
                'credentials' => [
                    'store_id' => $validated['sslcommerz']['store_id'] ?? null,
                    'store_password' => $this->resolveSecret(
                        $validated['sslcommerz']['store_password'] ?? null,
                        $sslCommerzCredentials['store_password'] ?? null,
                    ),
                    'mode' => $validated['sslcommerz']['mode'] ?? 'sandbox',
                ],
                'notes' => null,
            ])->save();

            $manual->fill([
                'name' => 'Manual',
                'status' => $validated['manual']['status'],
                'credentials' => [],
                'notes' => $validated['manual']['notes'] ?? null,
            ])->save();
        });

        return redirect()
            ->route('admin.payment-settings.edit')
            ->with('status', 'Payment gateway settings updated successfully.');
    }

    private function resolveSecret(mixed $incomingValue, mixed $existingValue): ?string
    {
        $normalized = trim((string) $incomingValue);

        if ($normalized !== '') {
            return $normalized;
        }

        if (is_string($existingValue) && trim($existingValue) !== '') {
            return $existingValue;
        }

        return null;
    }

    private function hasCredential(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
