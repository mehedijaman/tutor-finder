<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\NormalizesSmtpCredentials;
use App\Models\SmtpSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SmtpSettingUpdateRequest extends FormRequest
{
    use NormalizesSmtpCredentials;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare incoming data before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->normalizeSmtpSettingInput();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $smtpSettingId = $this->route('smtpSetting')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('smtp_settings', 'name')->ignore($smtpSettingId),
            ],
            'driver' => ['required', 'string', Rule::in(SmtpSetting::availableDrivers())],
            'from_address' => ['nullable', 'email', 'max:255'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'credentials_json' => ['nullable', 'string'],
            'credentials' => ['nullable', 'array'],
            'credentials.*' => ['nullable', 'string', 'max:1000'],
            'credential_items' => ['nullable', 'array'],
            'credential_items.*.key' => ['nullable', 'string', 'max:120'],
            'credential_items.*.value' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Configure after validation hooks.
     *
     * @return array<int, \Closure(\Illuminate\Validation\Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->validateSmtpCredentials($validator);
            },
        ];
    }
}
