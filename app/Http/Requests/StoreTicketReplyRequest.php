<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketReplyRequest extends FormRequest
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
            'message' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['image', 'max:5120', 'mimes:jpg,jpeg,png,gif'],
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
            'message.required' => 'Please enter a reply message.',
            'message.max' => 'Message cannot exceed 5000 characters.',
            'attachments.max' => 'You can attach up to 3 images.',
            'attachments.*.image' => 'Attachments must be images.',
            'attachments.*.max' => 'Each image must be under 5MB.',
            'attachments.*.mimes' => 'Allowed image formats: JPG, PNG, GIF.',
        ];
    }
}
