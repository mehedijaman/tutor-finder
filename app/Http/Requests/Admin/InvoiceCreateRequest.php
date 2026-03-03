<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceCreateRequest extends FormRequest
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
            'currency' => strtoupper(trim((string) $this->input('currency', 'BDT'))),
            'notes' => $this->nullableTrimmedString($this->input('notes')),
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
            'amount' => ['nullable', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'max:10'],
            'due_at' => ['nullable', 'date', 'after_or_equal:today'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Configure custom validation rules.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $dueAt = $this->input('due_at');
            $expiresAt = $this->input('expires_at');

            if ($dueAt && $expiresAt && strtotime((string) $expiresAt) < strtotime((string) $dueAt)) {
                $validator->errors()->add('expires_at', 'Invoice expiry must be equal to or after due date.');
            }
        });
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
