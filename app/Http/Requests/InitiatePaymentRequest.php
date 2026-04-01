<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'invoice.required' => 'Invoice is required for payment.',
            'invoice.integer' => 'Invalid invoice selected.',
        ];
    }

    public function getInvoice(): ?Invoice
    {
        $invoiceId = $this->route('invoice');

        if ($invoiceId === null) {
            return null;
        }

        return Invoice::query()->find($invoiceId);
    }
}
