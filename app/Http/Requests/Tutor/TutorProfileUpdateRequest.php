<?php

namespace App\Http\Requests\Tutor;

use App\Enums\ProfileStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class TutorProfileUpdateRequest extends FormRequest
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
            'gender' => $this->nullableTrimmedString($this->input('gender')),
            'present_address' => $this->nullableTrimmedString($this->input('present_address')),
            'permanent_address' => $this->nullableTrimmedString($this->input('permanent_address')),
            'nid_no' => $this->nullableTrimmedString($this->input('nid_no')),
            'bio' => $this->nullableTrimmedString($this->input('bio')),
            'available_time' => $this->nullableTrimmedString($this->input('available_time')),
            'status' => strtolower(trim((string) $this->input('status', ProfileStatus::Active->value))),
            'preferred_tuition_types' => $this->normalizeIntegerArray($this->input('preferred_tuition_types')),
            'preferred_categories' => $this->normalizeIntegerArray($this->input('preferred_categories')),
            'preferred_classes' => $this->normalizeIntegerArray($this->input('preferred_classes')),
            'preferred_subjects' => $this->normalizeIntegerArray($this->input('preferred_subjects')),
            'preferred_locations' => $this->normalizeIntegerArray($this->input('preferred_locations')),
            'available_days' => $this->normalizeDays($this->input('available_days')),
            'educations' => $this->normalizeEducations($this->input('educations')),
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
            'gender' => ['nullable', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'present_address' => ['nullable', 'string'],
            'permanent_address' => ['nullable', 'string'],
            'nid_no' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string'],
            'preferred_tuition_types' => ['nullable', 'array'],
            'preferred_tuition_types.*' => ['required', 'integer', 'min:1'],
            'preferred_categories' => ['nullable', 'array'],
            'preferred_categories.*' => ['required', 'integer', 'min:1'],
            'preferred_classes' => ['nullable', 'array'],
            'preferred_classes.*' => ['required', 'integer', 'min:1'],
            'preferred_subjects' => ['nullable', 'array'],
            'preferred_subjects.*' => ['required', 'integer', 'min:1'],
            'preferred_locations' => ['nullable', 'array'],
            'preferred_locations.*' => ['required', 'integer', 'min:1'],
            'expected_salary_min' => ['nullable', 'numeric', 'min:0'],
            'expected_salary_max' => ['nullable', 'numeric', 'min:0'],
            'available_days' => ['nullable', 'array'],
            'available_days.*' => ['required', Rule::in($this->allowedDays())],
            'available_time' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', new Enum(ProfileStatus::class)],
            'educations' => ['nullable', 'array'],
            'educations.*.id' => [
                'nullable',
                'integer',
                Rule::exists('tutor_educations', 'id')->where(fn ($query) => $query->where('user_id', $this->user()?->getKey())),
            ],
            'educations.*.degree' => ['required_with:educations', 'string', 'max:150'],
            'educations.*.institute' => ['required_with:educations', 'string', 'max:255'],
            'educations.*.department' => ['nullable', 'string', 'max:150'],
            'educations.*.graduation_year' => ['nullable', 'integer', 'between:1900,'.(date('Y') + 10)],
            'educations.*.result' => ['nullable', 'string', 'max:100'],
            'educations.*.is_current' => ['required', 'boolean'],
            'educations.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Configure additional validation hooks.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $salaryMin = $this->input('expected_salary_min');
            $salaryMax = $this->input('expected_salary_max');

            if ($salaryMin !== null && $salaryMax !== null && (float) $salaryMin > (float) $salaryMax) {
                $validator->errors()->add('expected_salary_min', 'Minimum expected salary must be less than or equal to maximum expected salary.');
            }

            $educationIds = collect($this->input('educations', []))
                ->pluck('id')
                ->filter()
                ->values();

            if ($educationIds->count() !== $educationIds->unique()->count()) {
                $validator->errors()->add('educations', 'Duplicate education records are not allowed.');
            }
        });
    }

    /**
     * Get normalized education records.
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeEducations(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn (mixed $item): bool => is_array($item))
            ->values()
            ->map(function (array $education, int $index): array {
                return [
                    'id' => isset($education['id']) && (int) $education['id'] > 0 ? (int) $education['id'] : null,
                    'degree' => trim((string) ($education['degree'] ?? '')),
                    'institute' => trim((string) ($education['institute'] ?? '')),
                    'department' => $this->nullableTrimmedString($education['department'] ?? null),
                    'graduation_year' => isset($education['graduation_year']) && trim((string) $education['graduation_year']) !== ''
                        ? (int) $education['graduation_year']
                        : null,
                    'result' => $this->nullableTrimmedString($education['result'] ?? null),
                    'is_current' => filter_var($education['is_current'] ?? false, FILTER_VALIDATE_BOOL),
                    'sort_order' => isset($education['sort_order']) ? max(0, (int) $education['sort_order']) : $index,
                ];
            })
            ->all();
    }

    /**
     * Normalize integer array values.
     *
     * @return array<int, int>
     */
    private function normalizeIntegerArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn (mixed $item): int => (int) $item)
            ->filter(fn (int $item): bool => $item > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Normalize available day list.
     *
     * @return array<int, string>
     */
    private function normalizeDays(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn (mixed $item): string => strtolower(trim((string) $item)))
            ->filter(fn (string $day): bool => in_array($day, $this->allowedDays(), true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Get allowed day options.
     *
     * @return list<string>
     */
    private function allowedDays(): array
    {
        return ['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'];
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
