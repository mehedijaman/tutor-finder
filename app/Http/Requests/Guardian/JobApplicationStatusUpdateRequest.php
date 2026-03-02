<?php

namespace App\Http\Requests\Guardian;

use App\Models\TuitionJobApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobApplicationStatusUpdateRequest extends FormRequest
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
            'status' => strtolower(trim((string) $this->input('status'))),
            'guardian_note' => $this->filled('guardian_note')
                ? trim((string) $this->input('guardian_note'))
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
            'status' => [
                'required',
                'string',
                Rule::in([TuitionJobApplication::STATUS_SHORTLISTED, TuitionJobApplication::STATUS_REJECTED]),
            ],
            'guardian_note' => ['nullable', 'string', 'max:5000'],
            'reviewed_by' => ['prohibited'],
            'reviewed_at' => ['prohibited'],
            'tutor_id' => ['prohibited'],
            'tuition_job_id' => ['prohibited'],
        ];
    }
}
