<?php

namespace App\Http\Requests\Admin\Tuition\Taxonomies;

use App\Enums\TaxonomyStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountryStoreRequest extends FormRequest
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
            'slug' => trim((string) $this->input('slug')),
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
            'name' => ['required', 'string', 'max:255', Rule::unique('countries', 'name')],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in([TaxonomyStatus::Active->value, TaxonomyStatus::Inactive->value])],
        ];
    }
}
