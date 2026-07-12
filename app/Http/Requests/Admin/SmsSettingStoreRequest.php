<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\NormalizesSmsCredentials;
use App\Models\SmsSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SmsSettingStoreRequest extends FormRequest
{
    use NormalizesSmsCredentials;

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
        $this->normalizeSmsSettingInput();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('sms_settings', 'name')],
            'provider' => ['required', 'string', Rule::in(SmsSetting::availableProviders())],
            'credentials_json' => ['nullable', 'string'],
            'credentials' => ['nullable', 'array', 'min:1'],
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
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->validateSmsCredentials($validator);
            },
        ];
    }
}
