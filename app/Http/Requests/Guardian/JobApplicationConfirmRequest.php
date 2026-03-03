<?php

namespace App\Http\Requests\Guardian;

use Illuminate\Foundation\Http\FormRequest;

class JobApplicationConfirmRequest extends FormRequest
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
            'month1_escrow_required' => ['required', 'boolean'],
            'month1_escrow_amount' => ['nullable', 'numeric', 'min:1', 'required_if:month1_escrow_required,1'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'month1_escrow_required' => filter_var($this->input('month1_escrow_required'), FILTER_VALIDATE_BOOL),
            'month1_escrow_amount' => $this->filled('month1_escrow_amount')
                ? (float) $this->input('month1_escrow_amount')
                : null,
            'notes' => $this->filled('notes')
                ? trim((string) $this->input('notes'))
                : null,
        ]);
    }
}
