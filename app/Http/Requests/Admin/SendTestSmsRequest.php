<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendTestSmsRequest extends FormRequest
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
        $mobile = preg_replace('/\s+/', '', (string) $this->input('mobile'));
        $message = trim((string) $this->input('message'));

        $this->merge([
            'mobile' => $mobile,
            'message' => $message,
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
            'mobile' => ['required', 'string', 'max:20', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'message' => ['required', 'string', 'max:640'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.required' => 'A mobile number is required.',
            'mobile.regex' => 'Use a valid Bangladeshi mobile number format.',
            'message.required' => 'A test SMS message is required.',
        ];
    }
}
