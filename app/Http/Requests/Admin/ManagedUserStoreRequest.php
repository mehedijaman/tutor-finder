<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ManagedUserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
            'status' => ['required', Rule::in([UserStatus::Active, UserStatus::Suspended, UserStatus::PendingVerification])],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date'],
            'present_address' => ['nullable', 'string'],
            'permanent_address' => ['nullable', 'string'],
            'nid_no' => ['nullable', 'string', 'max:50'],
            'bio' => ['nullable', 'string'],
            'preferred_tuition_types' => ['nullable', 'array'],
            'preferred_categories' => ['nullable', 'array'],
            'preferred_classes' => ['nullable', 'array'],
            'preferred_subjects' => ['nullable', 'array'],
            'preferred_locations' => ['nullable', 'array'],
            'expected_salary_min' => ['nullable', 'numeric', 'min:0'],
            'expected_salary_max' => ['nullable', 'numeric', 'min:0'],
            'available_days' => ['nullable', 'array'],
            'available_time' => ['nullable', 'string'],

            // Guardian profile fields
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone_alt' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],

            'educations' => ['nullable', 'array'],
            'educations.*.degree' => ['required', 'string', 'max:255'],
            'educations.*.institute' => ['required', 'string', 'max:255'],
            'educations.*.department' => ['nullable', 'string', 'max:255'],
            'educations.*.graduation_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'educations.*.result' => ['nullable', 'string', 'max:50'],
            'educations.*.is_current' => ['nullable', 'boolean'],
        ];
    }
}
