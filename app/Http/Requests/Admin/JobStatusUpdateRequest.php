<?php

namespace App\Http\Requests\Admin;

use App\Models\TuitionJob;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobStatusUpdateRequest extends FormRequest
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
            'reason' => trim((string) $this->input('reason')),
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
            'status' => ['required', 'string', Rule::in(TuitionJob::statuses())],
            'reason' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
