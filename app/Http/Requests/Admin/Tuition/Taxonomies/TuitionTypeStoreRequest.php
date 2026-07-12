<?php

namespace App\Http\Requests\Admin\Tuition\Taxonomies;

use App\Enums\TaxonomyStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TuitionTypeStoreRequest extends FormRequest
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
            'description' => $this->nullableTrimmedString($this->input('description')),
            'status' => strtolower(trim((string) $this->input('status', TaxonomyStatus::Active->value))),
            'sort_order' => $this->normalizeSortOrder($this->input('sort_order')),
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
            'name' => ['required', 'string', 'max:255', Rule::unique('tuition_types', 'name')],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in([TaxonomyStatus::Active->value, TaxonomyStatus::Inactive->value])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function nullableTrimmedString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    protected function normalizeSortOrder(mixed $value): int
    {
        if ($value === null || trim((string) $value) === '') {
            return 0;
        }

        return max(0, (int) $value);
    }
}
