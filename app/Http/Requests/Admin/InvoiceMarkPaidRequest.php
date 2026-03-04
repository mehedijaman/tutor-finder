<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentGatewayType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class InvoiceMarkPaidRequest extends FormRequest
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
            'payment_gateway' => strtolower(trim((string) $this->input('payment_gateway', PaymentGatewayType::Manual->value))),
            'payment_method' => trim((string) $this->input('payment_method', 'manual')),
            'payment_reference' => $this->nullableTrimmedString($this->input('payment_reference')),
            'notes' => $this->nullableTrimmedString($this->input('notes')),
            'paid_at' => $this->input('paid_at') ?: now()->toDateTimeString(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_gateway' => ['required', new Enum(PaymentGatewayType::class)],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
