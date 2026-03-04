<?php

namespace App\Http\Requests\Guardian;

use App\Enums\ApplicationStatus;
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
            'cancel_reason' => $this->filled('cancel_reason')
                ? trim((string) $this->input('cancel_reason'))
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
                Rule::in([ApplicationStatus::Shortlisted->value, ApplicationStatus::Cancelled->value]),
            ],
            'cancel_reason' => ['nullable', 'string', 'max:5000'],
            'tutor_user_id' => ['prohibited'],
            'job_id' => ['prohibited'],
        ];
    }
}
