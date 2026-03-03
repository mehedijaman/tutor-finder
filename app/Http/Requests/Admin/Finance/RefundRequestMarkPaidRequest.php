<?php

namespace App\Http\Requests\Admin\Finance;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RefundRequestMarkPaidRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'gateway' => [
                'required',
                Rule::in([Invoice::GATEWAY_MANUAL, Invoice::GATEWAY_BKASH, Invoice::GATEWAY_SSLCOMMERZ]),
            ],
            'provider_txn_id' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'gateway' => strtolower(trim((string) $this->input('gateway', Invoice::GATEWAY_MANUAL))),
            'provider_txn_id' => $this->filled('provider_txn_id')
                ? trim((string) $this->input('provider_txn_id'))
                : null,
            'note' => $this->filled('note')
                ? trim((string) $this->input('note'))
                : null,
        ]);
    }
}
