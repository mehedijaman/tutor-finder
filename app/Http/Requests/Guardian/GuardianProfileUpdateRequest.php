<?php

namespace App\Http\Requests\Guardian;

use App\Enums\TaxonomyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardianProfileUpdateRequest extends FormRequest
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
            'name' => trim((string) $this->input('name')),
            'phone' => $this->nullableTrimmedString($this->input('phone')),
            'phone_alt' => $this->nullableTrimmedString($this->input('phone_alt')),
            'guardian_name' => $this->nullableTrimmedString($this->input('guardian_name')),
            'address' => $this->nullableTrimmedString($this->input('address')),
            'occupation' => $this->nullableTrimmedString($this->input('occupation')),
            'notes' => $this->nullableTrimmedString($this->input('notes')),
            'status' => strtolower(trim((string) $this->input('status', TaxonomyStatus::Active->value))),
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
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($this->user()?->getKey())],
            'phone_alt' => ['nullable', 'string', 'max:30'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in([TaxonomyStatus::Active->value, TaxonomyStatus::Inactive->value])],
        ];
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
