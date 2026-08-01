<?php

namespace App\Http\Requests\Guardian;

use App\Enums\TaxonomyStatus;
use Illuminate\Contracts\Validation\ValidationRule;
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
            'emergency_contact' => $this->nullableTrimmedString($this->input('emergency_contact')),
            'guardian_name' => $this->nullableTrimmedString($this->input('guardian_name')),
            'relationship_to_student' => $this->nullableTrimmedString($this->input('relationship_to_student')),
            'address' => $this->nullableTrimmedString($this->input('address')),
            'city' => $this->nullableTrimmedString($this->input('city')),
            'area' => $this->nullableTrimmedString($this->input('area')),
            'occupation' => $this->nullableTrimmedString($this->input('occupation')),
            'notes' => $this->nullableTrimmedString($this->input('notes')),
            'preferred_contact_time' => $this->nullableTrimmedString($this->input('preferred_contact_time')),
            'status' => strtolower(trim((string) $this->input('status', TaxonomyStatus::Active->value))),
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
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($this->user()?->getKey())],
            'phone_alt' => ['nullable', 'string', 'max:30'],
            'emergency_contact' => ['nullable', 'string', 'max:30'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'relationship_to_student' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'preferred_contact_time' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in([TaxonomyStatus::Active->value, TaxonomyStatus::Inactive->value])],
        ];
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
