<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Contracts\Validation\ValidationRule;
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
            'expected_salary_amount' => $this->filled('expected_salary_amount')
                ? (float) $this->input('expected_salary_amount')
                : null,
            'salary_currency' => $this->filled('salary_currency')
                ? strtoupper(trim((string) $this->input('salary_currency')))
                : 'BDT',
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
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'expected_salary_amount' => ['nullable', 'numeric', 'min:0'],
            'salary_currency' => ['nullable', 'string', 'size:3'],
            'status' => ['prohibited'],
            'tutor_user_id' => ['prohibited'],
            'job_id' => ['prohibited'],
        ];
    }
}
