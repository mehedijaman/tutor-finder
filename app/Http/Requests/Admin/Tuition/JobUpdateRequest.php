<?php

namespace App\Http\Requests\Admin\Tuition;

use App\Enums\JobGender;
use App\Enums\JobStatus;
use App\Models\Area;
use App\Models\City;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class JobUpdateRequest extends FormRequest
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
        $normalizedDays = $this->normalizeTuitionDays($this->input('tuition_days'));

        $this->merge([
            'title' => trim((string) $this->input('title')),
            'description' => trim((string) $this->input('description')),
            'location' => trim((string) $this->input('location')),
            'student_gender' => strtolower(trim((string) $this->input('student_gender', JobGender::Any->value))),
            'tutor_gender' => strtolower(trim((string) $this->input('tutor_gender', JobGender::Any->value))),
            'salary_currency' => strtoupper(trim((string) $this->input('salary_currency', 'BDT'))),
            'salary_negotiable' => $this->boolean('salary_negotiable'),
            'status' => strtolower(trim((string) $this->input('status', JobStatus::Pending->value))),
            'tuition_days' => $normalizedDays,
            'days_per_week' => count($normalizedDays) > 0 ? count($normalizedDays) : null,
            'tuition_time' => trim((string) $this->input('tuition_time')),
            'tuition_duration' => trim((string) $this->input('tuition_duration')),
            'subject_ids' => $this->normalizeSubjectIds($this->input('subject_ids')),
            'tuition_type_id' => (int) $this->input('tuition_type_id'),
            'category_id' => (int) $this->input('category_id'),
            'class_id' => (int) $this->input('class_id'),
            'country_id' => (int) $this->input('country_id'),
            'city_id' => (int) $this->input('city_id'),
            'area_id' => $this->filled('area_id') ? (int) $this->input('area_id') : null,
            'guardian_id' => (int) $this->input('guardian_id'),
            'no_of_students' => $this->filled('no_of_students') ? (int) $this->input('no_of_students') : null,
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'guardian_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn (Builder $query): Builder => $query
                    ->whereNull('deleted_at')
                    ->where('role', 'guardian')
                    ->where('status', 'active')),
            ],
            'tuition_type_id' => [
                'required',
                'integer',
                Rule::exists('tuition_types', 'id')->where(fn (Builder $query): Builder => $query
                    ->whereNull('deleted_at')
                    ->where('status', 'active')),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn (Builder $query): Builder => $query
                    ->whereNull('deleted_at')
                    ->where('status', 'active')),
            ],
            'class_id' => [
                'required',
                'integer',
                Rule::exists('classes', 'id')->where(fn (Builder $query): Builder => $query
                    ->whereNull('deleted_at')
                    ->where('status', 'active')),
            ],
            'country_id' => [
                'required',
                'integer',
                Rule::exists('countries', 'id')->where(fn (Builder $query): Builder => $query
                    ->whereNull('deleted_at')
                    ->where('status', 'active')),
            ],
            'city_id' => [
                'required',
                'integer',
                Rule::exists('cities', 'id')->where(fn (Builder $query): Builder => $query
                    ->whereNull('deleted_at')
                    ->where('status', 'active')),
            ],
            'area_id' => [
                'nullable',
                'integer',
                Rule::exists('areas', 'id')->where(fn (Builder $query): Builder => $query
                    ->whereNull('deleted_at')
                    ->where('status', 'active')),
            ],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')->where(fn (Builder $query): Builder => $query
                    ->whereNull('deleted_at')
                    ->where('status', 'active')),
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'student_gender' => ['required', 'string', new Enum(JobGender::class)],
            'tutor_gender' => ['required', 'string', new Enum(JobGender::class)],
            'tuition_days' => ['nullable', 'array'],
            'tuition_days.*' => ['required', 'string', Rule::in($this->allowedDays())],
            'days_per_week' => ['nullable', 'integer', 'between:1,7'],
            'tuition_time' => ['nullable', 'string', 'max:100'],
            'tuition_duration' => ['nullable', 'string', 'max:100'],
            'no_of_students' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'salary_amount' => ['nullable', 'numeric', 'min:0'],
            'salary_currency' => ['nullable', 'string', 'max:10'],
            'salary_negotiable' => ['required', 'boolean'],
            'status' => ['required', 'string', Rule::in([JobStatus::Pending->value, JobStatus::Live->value])],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'view_count' => ['prohibited'],
            'confirmed_by' => ['prohibited'],
            'confirmed_at' => ['prohibited'],
        ];
    }

    /**
     * Configure additional validation rules.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $categoryId = (int) $this->input('category_id');
            $classId = (int) $this->input('class_id');
            $countryId = (int) $this->input('country_id');
            $cityId = (int) $this->input('city_id');
            $areaId = (int) ($this->input('area_id') ?? 0);
            $subjectIds = $this->normalizeSubjectIds($this->input('subject_ids'));

            if ($classId > 0 && $categoryId > 0) {
                $classMatchesCategory = SchoolClass::query()
                    ->whereKey($classId)
                    ->where('category_id', $categoryId)
                    ->whereNull('deleted_at')
                    ->exists();

                if (! $classMatchesCategory) {
                    $validator->errors()->add('class_id', 'Selected class does not belong to the selected category.');
                }
            }

            if ($cityId > 0 && $countryId > 0) {
                $cityMatchesCountry = City::query()
                    ->whereKey($cityId)
                    ->where('country_id', $countryId)
                    ->whereNull('deleted_at')
                    ->exists();

                if (! $cityMatchesCountry) {
                    $validator->errors()->add('city_id', 'Selected city does not belong to the selected country.');
                }
            }

            if ($areaId > 0 && $cityId > 0) {
                $areaMatchesCity = Area::query()
                    ->whereKey($areaId)
                    ->where('city_id', $cityId)
                    ->whereNull('deleted_at')
                    ->exists();

                if (! $areaMatchesCity) {
                    $validator->errors()->add('area_id', 'Selected area does not belong to the selected city.');
                }
            }

            if ($classId > 0 && count($subjectIds) > 0) {
                $matchedSubjectCount = Subject::query()
                    ->whereIn('id', $subjectIds)
                    ->where('class_id', $classId)
                    ->whereNull('deleted_at')
                    ->count();

                if ($matchedSubjectCount !== count($subjectIds)) {
                    $validator->errors()->add('subject_ids', 'All selected subjects must belong to the selected class.');
                }
            }
        });
    }

    /**
     * Normalize selected subject IDs.
     *
     * @return array<int, int>
     */
    private function normalizeSubjectIds(mixed $value): array
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
     * Normalize tuition days and remove invalid values.
     *
     * @return array<int, string>
     */
    private function normalizeTuitionDays(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn (mixed $item): string => strtolower(trim((string) $item)))
            ->filter(fn (string $item): bool => in_array($item, $this->allowedDays(), true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Get allowed tuition days.
     *
     * @return list<string>
     */
    private function allowedDays(): array
    {
        return ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    }
}
