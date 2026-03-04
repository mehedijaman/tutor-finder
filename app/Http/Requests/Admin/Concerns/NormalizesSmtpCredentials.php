<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\SmtpSetting;
use Illuminate\Validation\Validator;

trait NormalizesSmtpCredentials
{
    /**
     * Normalize SMTP setting input before validation.
     */
    protected function normalizeSmtpSettingInput(): void
    {
        $driver = $this->input('driver');

        if (! is_string($driver) || ! in_array($driver, SmtpSetting::availableDrivers(), true)) {
            return;
        }

        $credentialItems = $this->input('credential_items');

        if (is_array($credentialItems)) {
            $normalized = [];

            foreach ($credentialItems as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $key = trim((string) ($item['key'] ?? ''));
                $value = (string) ($item['value'] ?? '');

                if ($key !== '') {
                    $normalized[$key] = $value;
                }
            }

            $this->merge(['credentials' => $normalized]);
        }

        $json = $this->input('credentials_json');

        if (is_string($json) && $json !== '') {
            $decoded = json_decode($json, true);

            if (is_array($decoded)) {
                $this->merge(['credentials' => $decoded]);
            }
        }
    }

    /**
     * Validate SMTP credentials after rules pass.
     */
    protected function validateSmtpCredentials(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $driver = $this->input('driver');
        $credentials = $this->input('credentials') ?? [];
        $isActive = $this->boolean('is_active');

        if (! $isActive) {
            return;
        }

        $requiredKeys = SmtpSetting::requiredCredentialKeys($driver);

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $credentials) || trim((string) $credentials[$key]) === '') {
                $validator->errors()->add(
                    "credentials.{$key}",
                    "The {$key} field is required for {$driver} driver."
                );
            }
        }
    }
}
