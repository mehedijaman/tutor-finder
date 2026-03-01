<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SmsSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'provider',
        'credentials',
        'is_default',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope query to active gateway settings.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Return supported provider class basenames from config/sms.php.
     *
     * @return array<int, string>
     */
    public static function availableProviders(): array
    {
        return collect(array_keys(config('sms.providers', [])))
            ->map(fn ($provider): string => class_basename((string) $provider))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Return provider metadata derived from package config and docs.
     *
     * @return array<int, array{
     *     name: string,
     *     fields: array<int, array{key: string, label: string, required: bool, sensitive: bool, description: string, placeholder: string}>,
     *     required_fields: array<int, string>
     * }>
     */
    public static function providerOptions(): array
    {
        return collect(self::providerCredentialKeys())
            ->map(function (array $keys, string $provider): array {
                $requiredFields = self::requiredCredentialKeys($provider);

                $fields = collect($keys)
                    ->map(function (string $key) use ($requiredFields): array {
                        return [
                            'key' => $key,
                            'label' => self::formatCredentialLabel($key),
                            'required' => in_array($key, $requiredFields, true),
                            'sensitive' => self::isSensitiveCredentialField($key),
                            'description' => self::credentialFieldDescription($key),
                            'placeholder' => self::credentialFieldPlaceholder($key),
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'name' => $provider,
                    'fields' => $fields,
                    'required_fields' => $requiredFields,
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();
    }

    /**
     * Resolve required credential keys by provider name.
     *
     * @return array<int, string>
     */
    public static function requiredCredentialKeys(string $provider): array
    {
        $credentialKeys = self::providerCredentialKeys()[$provider] ?? [];
        $optionalKeys = self::optionalCredentialKeys()[$provider] ?? [];

        return collect($credentialKeys)
            ->reject(fn (string $key): bool => in_array($key, $optionalKeys, true))
            ->values()
            ->all();
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected static function providerCredentialKeys(): array
    {
        return collect(config('sms.providers', []))
            ->mapWithKeys(function ($credentials, $providerClass): array {
                if (! is_array($credentials)) {
                    return [];
                }

                return [class_basename((string) $providerClass) => array_keys($credentials)];
            })
            ->all();
    }

    /**
     * Optional keys mapped from package docs/config defaults.
     *
     * @return array<string, array<int, string>>
     */
    protected static function optionalCredentialKeys(): array
    {
        return [
            'Ssl' => ['batch_csms_id', 'batch_csms_id '],
            'MimSms' => ['TransactionType', 'CampaignId', 'CampaignName'],
            'Lpeek' => ['is_unicode', 'transactionType'],
            'QuickSms' => ['scheduledDateTime'],
            'SmsNet24' => ['route_id', 'sms_type_id'],
            'Alpha' => ['sender_id', 'schedule'],
            'TwentyFourBulkSmsBD' => ['sender_id'],
        ];
    }

    protected static function isSensitiveCredentialField(string $key): bool
    {
        return (bool) preg_match('/(token|secret|password|passwd|api[_-]?key|bearer|privatekey|x-app-secret|key)$/i', $key);
    }

    protected static function formatCredentialLabel(string $key): string
    {
        return (string) Str::of($key)
            ->replace(['_', '-'], ' ')
            ->trim()
            ->title();
    }

    protected static function credentialFieldDescription(string $key): string
    {
        return match (Str::lower(trim($key))) {
            'api_token', 'token' => 'API token from your SMS gateway dashboard.',
            'api_key', 'apikey' => 'API key issued by your SMS provider.',
            'api_secret', 'secretkey', 'x-app-secret' => 'API secret/key pair value from provider.',
            'sid', 'senderid', 'sender_id', 'sendername', 'masking', 'masking_name', 'mask' => 'Approved sender ID / mask configured at the provider.',
            'csms_id', 'batch_csms_id' => 'Campaign SMS identifier (often unique per request).',
            'username', 'user', 'userid', 'user_name' => 'Provider account username/user ID.',
            'password', 'passwd', 'userpassword', 'user_password', 'pwd' => 'Provider account password/secret.',
            default => 'Credential value required by the selected gateway provider.',
        };
    }

    protected static function credentialFieldPlaceholder(string $key): string
    {
        return match (Str::lower(trim($key))) {
            'api_token', 'token' => 'Enter API token',
            'api_key', 'apikey', 'x-app-key' => 'Enter API key',
            'api_secret', 'secretkey', 'x-app-secret' => 'Enter API secret',
            'sid', 'senderid', 'sender_id', 'sendername', 'masking', 'masking_name', 'mask' => 'Enter sender ID/mask',
            'csms_id', 'batch_csms_id' => 'Enter campaign SMS ID',
            'username', 'user', 'userid', 'user_name' => 'Enter username',
            'password', 'passwd', 'userpassword', 'user_password', 'pwd' => 'Enter password',
            default => 'Enter value',
        };
    }
}
