<?php

namespace App\Http\Requests\Admin\Tuition\Taxonomies;

use App\Enums\TaxonomyStatus;
use App\Models\Country;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountryUpdateRequest extends FormRequest
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
        /** @var Country|null $country */
        $country = $this->route('country');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('countries', 'name')->ignore($country)],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in([TaxonomyStatus::Active->value, TaxonomyStatus::Inactive->value])],
        ];
    }
}
