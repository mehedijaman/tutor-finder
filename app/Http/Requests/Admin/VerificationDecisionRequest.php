<?php

namespace App\Http\Requests\Admin;

use App\Models\VerificationRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerificationDecisionRequest extends FormRequest
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
        if ($this->routeIs('admin.verifications.approve')) {
            return;
        }

        $this->merge([
            'reason' => trim((string) $this->input('reason')),
            'decision_status' => strtolower(trim((string) $this->input('decision_status', VerificationRequest::STATUS_REJECTED))),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->routeIs('admin.verifications.approve')) {
            return [
                'reason' => ['prohibited'],
                'decision_status' => ['prohibited'],
            ];
        }

        return [
            'reason' => ['required', 'string', 'max:5000'],
            'decision_status' => [
                'required',
                Rule::in([
                    VerificationRequest::STATUS_REJECTED,
                    VerificationRequest::STATUS_CANCELLED,
                ]),
            ],
        ];
    }
}
