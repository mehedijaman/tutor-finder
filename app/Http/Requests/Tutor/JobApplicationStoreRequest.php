<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;

class JobApplicationStoreRequest extends FormRequest
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
            'cover_letter' => $this->filled('cover_letter')
                ? trim((string) $this->input('cover_letter'))
                : null,
            'expected_salary' => $this->filled('expected_salary')
                ? (float) $this->input('expected_salary')
                : null,
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
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'expected_salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['prohibited'],
            'tutor_id' => ['prohibited'],
            'tuition_job_id' => ['prohibited'],
            'reviewed_by' => ['prohibited'],
            'reviewed_at' => ['prohibited'],
        ];
    }
}
