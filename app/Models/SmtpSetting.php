<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'driver',
        'credentials',
        'from_address',
        'from_name',
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
     * Scope query to active settings.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to default setting.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the default active SMTP setting.
     */
    public static function getDefault(): ?self
    {
        return static::query()
            ->active()
            ->default()
            ->first();
    }

    /**
     * Return supported mail drivers.
     *
     * @return array<int, string>
     */
    public static function availableDrivers(): array
    {
        return ['smtp', 'ses', 'mailgun', 'postmark', 'resend', 'sendmail', 'log'];
    }

    /**
     * Return driver metadata with credential field definitions.
     *
     * @return array<string, array{name: string, fields: array<int, array{key: string, label: string, required: bool, sensitive: bool, description: string, placeholder: string, type: string}>}>
     */
    public static function driverMetadata(): array
    {
        return [
            'smtp' => [
                'name' => 'SMTP',
                'fields' => [
                    [
                        'key' => 'host',
                        'label' => 'SMTP Host',
                        'required' => true,
                        'sensitive' => false,
                        'description' => 'The hostname of your SMTP server',
                        'placeholder' => 'smtp.example.com',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'port',
                        'label' => 'SMTP Port',
                        'required' => true,
                        'sensitive' => false,
                        'description' => 'Common ports: 587 (TLS), 465 (SSL), 25',
                        'placeholder' => '587',
                        'type' => 'number',
                    ],
                    [
                        'key' => 'encryption',
                        'label' => 'Encryption',
                        'required' => false,
                        'sensitive' => false,
                        'description' => 'TLS is recommended for security',
                        'placeholder' => 'tls',
                        'type' => 'select',
                        'options' => ['tls', 'ssl', 'none'],
                    ],
                    [
                        'key' => 'username',
                        'label' => 'Username',
                        'required' => false,
                        'sensitive' => false,
                        'description' => 'SMTP authentication username',
                        'placeholder' => 'user@example.com',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'password',
                        'label' => 'Password',
                        'required' => false,
                        'sensitive' => true,
                        'description' => 'SMTP authentication password',
                        'placeholder' => '••••••••',
                        'type' => 'password',
                    ],
                ],
            ],
            'ses' => [
                'name' => 'Amazon SES',
                'fields' => [
                    [
                        'key' => 'key',
                        'label' => 'AWS Access Key ID',
                        'required' => true,
                        'sensitive' => false,
                        'description' => 'Your AWS access key',
                        'placeholder' => 'AKIAIOSFODNN7EXAMPLE',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'secret',
                        'label' => 'AWS Secret Access Key',
                        'required' => true,
                        'sensitive' => true,
                        'description' => 'Your AWS secret key',
                        'placeholder' => '••••••••',
                        'type' => 'password',
                    ],
                    [
                        'key' => 'region',
                        'label' => 'AWS Region',
                        'required' => true,
                        'sensitive' => false,
                        'description' => 'SES region (e.g., us-east-1)',
                        'placeholder' => 'us-east-1',
                        'type' => 'text',
                    ],
                ],
            ],
            'mailgun' => [
                'name' => 'Mailgun',
                'fields' => [
                    [
                        'key' => 'domain',
                        'label' => 'Domain',
                        'required' => true,
                        'sensitive' => false,
                        'description' => 'Your Mailgun domain',
                        'placeholder' => 'mg.example.com',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'secret',
                        'label' => 'API Key',
                        'required' => true,
                        'sensitive' => true,
                        'description' => 'Your Mailgun API key',
                        'placeholder' => 'key-••••••••',
                        'type' => 'password',
                    ],
                    [
                        'key' => 'endpoint',
                        'label' => 'API Endpoint',
                        'required' => false,
                        'sensitive' => false,
                        'description' => 'EU endpoint: api.eu.mailgun.net',
                        'placeholder' => 'api.mailgun.net',
                        'type' => 'text',
                    ],
                ],
            ],
            'postmark' => [
                'name' => 'Postmark',
                'fields' => [
                    [
                        'key' => 'token',
                        'label' => 'Server API Token',
                        'required' => true,
                        'sensitive' => true,
                        'description' => 'Your Postmark server token',
                        'placeholder' => '••••••••',
                        'type' => 'password',
                    ],
                ],
            ],
            'resend' => [
                'name' => 'Resend',
                'fields' => [
                    [
                        'key' => 'key',
                        'label' => 'API Key',
                        'required' => true,
                        'sensitive' => true,
                        'description' => 'Your Resend API key',
                        'placeholder' => 're_••••••••',
                        'type' => 'password',
                    ],
                ],
            ],
            'sendmail' => [
                'name' => 'Sendmail',
                'fields' => [
                    [
                        'key' => 'path',
                        'label' => 'Sendmail Path',
                        'required' => false,
                        'sensitive' => false,
                        'description' => 'Path to sendmail binary',
                        'placeholder' => '/usr/sbin/sendmail -bs -i',
                        'type' => 'text',
                    ],
                ],
            ],
            'log' => [
                'name' => 'Log (Testing)',
                'fields' => [],
            ],
        ];
    }

    /**
     * Get required credential keys for a driver.
     *
     * @return array<int, string>
     */
    public static function requiredCredentialKeys(string $driver): array
    {
        $metadata = static::driverMetadata()[$driver] ?? null;

        if ($metadata === null) {
            return [];
        }

        return collect($metadata['fields'])
            ->filter(fn (array $field): bool => $field['required'] === true)
            ->pluck('key')
            ->all();
    }

    /**
     * Check if this setting has all required credentials configured.
     */
    public function hasCompleteCredentials(): bool
    {
        $required = static::requiredCredentialKeys($this->driver);
        $credentials = is_array($this->credentials) ? $this->credentials : [];

        foreach ($required as $key) {
            if (! array_key_exists($key, $credentials) || trim((string) $credentials[$key]) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Mark this setting as the default and unset others.
     */
    public function markAsDefault(): void
    {
        static::query()
            ->where('id', '!=', $this->id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Get the mail configuration array for this setting.
     *
     * @return array<string, mixed>
     */
    public function toMailConfig(): array
    {
        $credentials = is_array($this->credentials) ? $this->credentials : [];

        $config = [
            'transport' => $this->driver,
            'from' => [
                'address' => $this->from_address,
                'name' => $this->from_name,
            ],
        ];

        match ($this->driver) {
            'smtp' => $config = array_merge($config, [
                'host' => $credentials['host'] ?? null,
                'port' => (int) ($credentials['port'] ?? 587),
                'encryption' => $credentials['encryption'] ?? 'tls',
                'username' => $credentials['username'] ?? null,
                'password' => $credentials['password'] ?? null,
            ]),
            'ses' => $config = array_merge($config, [
                'key' => $credentials['key'] ?? null,
                'secret' => $credentials['secret'] ?? null,
                'region' => $credentials['region'] ?? 'us-east-1',
            ]),
            'mailgun' => $config = array_merge($config, [
                'domain' => $credentials['domain'] ?? null,
                'secret' => $credentials['secret'] ?? null,
                'endpoint' => $credentials['endpoint'] ?? 'api.mailgun.net',
            ]),
            'postmark' => $config = array_merge($config, [
                'token' => $credentials['token'] ?? null,
            ]),
            'resend' => $config = array_merge($config, [
                'key' => $credentials['key'] ?? null,
            ]),
            'sendmail' => $config = array_merge($config, [
                'path' => $credentials['path'] ?? '/usr/sbin/sendmail -bs -i',
            ]),
            default => null,
        };

        return $config;
    }
}
