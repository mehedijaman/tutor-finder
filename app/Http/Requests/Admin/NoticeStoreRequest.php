<?php

namespace App\Http\Requests\Admin;

use App\Enums\NoticeAudience;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NoticeStoreRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string'],
            'audience' => ['required', Rule::enum(NoticeAudience::class)],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a notice title.',
            'title.max' => 'The title cannot exceed 180 characters.',
            'body.required' => 'Please enter the notice content.',
            'audience.required' => 'Please select the target audience.',
            'expires_at.after_or_equal' => 'Expiry date must be today or in the future.',
        ];
    }
}
