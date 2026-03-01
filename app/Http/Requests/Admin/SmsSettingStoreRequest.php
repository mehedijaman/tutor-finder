<?php

namespace App\Http\Requests\Admin;

use App\Models\SmsSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SmsSettingStoreRequest extends FormRequest
{
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
        $this->merge([
            'is_default' => $this->boolean('is_default'),
            'is_active' => $this->boolean('is_active'),
        ]);

        $credentialsJson = $this->input('credentials_json');

        if (! is_string($credentialsJson)) {
            return;
        }

        $decoded = json_decode($credentialsJson, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $this->merge(['credentials' => $decoded]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('sms_settings', 'name')],
            'provider' => ['required', 'string', Rule::in(SmsSetting::availableProviders())],
            'credentials_json' => ['required', 'string'],
            'credentials' => ['required', 'array', 'min:1'],
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
                $credentialsJson = $this->input('credentials_json');

                if (! is_string($credentialsJson)) {
                    $validator->errors()->add('credentials_json', 'Credentials must be a valid JSON object.');

                    return;
                }

                $decoded = json_decode($credentialsJson, true);

                if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded) || array_is_list($decoded)) {
                    $validator->errors()->add('credentials_json', 'Credentials must be a valid JSON object.');
                }

                if ($this->boolean('is_default') && ! $this->boolean('is_active')) {
                    $validator->errors()->add('is_active', 'Default SMS setting must be active.');
                }
            },
        ];
    }
}
