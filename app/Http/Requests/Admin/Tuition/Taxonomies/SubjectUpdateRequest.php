<?php

namespace App\Http\Requests\Admin\Tuition\Taxonomies;

use App\Enums\TaxonomyStatus;
use App\Models\Subject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectUpdateRequest extends FormRequest
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
            'class_id' => (int) $this->input('class_id'),
            'name' => trim((string) $this->input('name')),
            'slug' => trim((string) $this->input('slug')),
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
        /** @var Subject|null $subject */
        $subject = $this->route('subject');
        $classId = (int) $this->input('class_id');

        return [
            'class_id' => [
                'required',
                'integer',
                Rule::exists('classes', 'id')->where(fn (Builder $query): Builder => $query->whereNull('deleted_at')),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects', 'name')
                    ->where(fn (Builder $query): Builder => $query->where('class_id', $classId))
                    ->ignore($subject),
            ],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in([TaxonomyStatus::Active->value, TaxonomyStatus::Inactive->value])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function normalizeSortOrder(mixed $value): int
    {
        if ($value === null || trim((string) $value) === '') {
            return 0;
        }

        return max(0, (int) $value);
    }
}
