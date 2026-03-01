<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\SmsSetting;
use Illuminate\Validation\Validator;

trait NormalizesSmsCredentials
{
    /**
     * Prepare credential and boolean fields before validation.
     */
    protected function normalizeSmsSettingInput(): void
    {
        $this->merge([
            'is_default' => $this->boolean('is_default'),
            'is_active' => $this->boolean('is_active'),
        ]);

        $credentials = $this->resolveCredentialMap();

        if ($credentials === null) {
            return;
        }

        $this->merge([
            'credentials' => $credentials,
        ]);
    }

    /**
     * Validate credential payload after the base rules are applied.
     */
    protected function validateSmsCredentials(Validator $validator): void
    {
        $credentials = $this->input('credentials');

        if (! is_array($credentials) || $credentials === [] || array_is_list($credentials)) {
            $validator->errors()->add('credentials', 'Credentials are required and must be a valid key/value object.');

            return;
        }

        $provider = (string) $this->input('provider');

        if ($provider !== '') {
            $missingKeys = collect(SmsSetting::requiredCredentialKeys($provider))
                ->filter(function (string $key) use ($credentials): bool {
                    if (! array_key_exists($key, $credentials)) {
                        return true;
                    }

                    return trim((string) $credentials[$key]) === '';
                })
                ->values()
                ->all();

            if ($missingKeys !== []) {
                $validator->errors()->add(
                    'credentials',
                    'Missing required credentials for '.$provider.': '.implode(', ', $missingKeys).'.',
                );
            }
        }

        if ($this->boolean('is_default') && ! $this->boolean('is_active')) {
            $validator->errors()->add('is_active', 'Default SMS setting must be active.');
        }
    }

    /**
     * @return array<string, string>|null
     */
    protected function resolveCredentialMap(): ?array
    {
        $credentials = $this->input('credentials');

        if (is_array($credentials) && ! array_is_list($credentials)) {
            return $this->normalizeCredentialObject($credentials);
        }

        $credentialItems = $this->input('credential_items');

        if (is_array($credentialItems)) {
            return $this->credentialItemsToObject($credentialItems);
        }

        $credentialsJson = $this->input('credentials_json');

        if (! is_string($credentialsJson)) {
            return null;
        }

        $decoded = json_decode($credentialsJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded) || array_is_list($decoded)) {
            return null;
        }

        return $this->normalizeCredentialObject($decoded);
    }

    /**
     * @param  array<int, array<string, mixed>>  $credentialItems
     * @return array<string, string>
     */
    protected function credentialItemsToObject(array $credentialItems): array
    {
        return collect($credentialItems)
            ->reduce(function (array $credentials, $item): array {
                if (! is_array($item)) {
                    return $credentials;
                }

                $key = trim((string) ($item['key'] ?? ''));

                if ($key === '') {
                    return $credentials;
                }

                $value = trim((string) ($item['value'] ?? ''));

                $credentials[$key] = $value;

                return $credentials;
            }, []);
    }

    /**
     * @param  array<string, mixed>  $credentials
     * @return array<string, string>
     */
    protected function normalizeCredentialObject(array $credentials): array
    {
        return collect($credentials)
            ->reduce(function (array $normalized, $value, $key): array {
                $normalizedKey = trim((string) $key);

                if ($normalizedKey === '') {
                    return $normalized;
                }

                $normalized[$normalizedKey] = trim((string) $value);

                return $normalized;
            }, []);
    }
}
