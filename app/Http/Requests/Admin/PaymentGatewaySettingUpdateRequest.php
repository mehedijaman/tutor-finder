<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentGatewaySettingUpdateRequest extends FormRequest
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
            'bkash' => [
                'status' => strtolower(trim((string) $this->input('bkash.status', 'active'))),
                'app_key' => $this->nullableTrimmedString($this->input('bkash.app_key')),
                'app_secret' => $this->nullableTrimmedString($this->input('bkash.app_secret')),
                'username' => $this->nullableTrimmedString($this->input('bkash.username')),
                'password' => $this->nullableTrimmedString($this->input('bkash.password')),
                'base_url' => $this->nullableTrimmedString($this->input('bkash.base_url')),
            ],
            'sslcommerz' => [
                'status' => strtolower(trim((string) $this->input('sslcommerz.status', 'active'))),
                'store_id' => $this->nullableTrimmedString($this->input('sslcommerz.store_id')),
                'store_password' => $this->nullableTrimmedString($this->input('sslcommerz.store_password')),
                'mode' => strtolower(trim((string) $this->input('sslcommerz.mode', 'sandbox'))),
            ],
            'manual' => [
                'status' => strtolower(trim((string) $this->input('manual.status', 'active'))),
                'notes' => $this->nullableTrimmedString($this->input('manual.notes')),
            ],
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bkash' => ['required', 'array'],
            'bkash.status' => ['required', Rule::in(['active', 'inactive'])],
            'bkash.app_key' => ['nullable', 'string', 'max:255'],
            'bkash.app_secret' => ['nullable', 'string', 'max:2000'],
            'bkash.username' => ['nullable', 'string', 'max:255'],
            'bkash.password' => ['nullable', 'string', 'max:2000'],
            'bkash.base_url' => ['nullable', 'url', 'max:2048'],

            'sslcommerz' => ['required', 'array'],
            'sslcommerz.status' => ['required', Rule::in(['active', 'inactive'])],
            'sslcommerz.store_id' => ['nullable', 'string', 'max:255'],
            'sslcommerz.store_password' => ['nullable', 'string', 'max:2000'],
            'sslcommerz.mode' => ['required', Rule::in(['sandbox', 'live'])],

            'manual' => ['required', 'array'],
            'manual.status' => ['required', Rule::in(['active', 'inactive'])],
            'manual.notes' => ['nullable', 'string'],
        ];
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
