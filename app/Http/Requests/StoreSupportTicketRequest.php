<?php

namespace App\Http\Requests;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportTicketRequest extends FormRequest
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
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(TicketCategory::class)],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
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
            'subject.required' => 'Please enter a ticket subject.',
            'subject.max' => 'Subject cannot exceed 255 characters.',
            'category.required' => 'Please select a category.',
            'priority.required' => 'Please select a priority level.',
            'message.required' => 'Please describe your issue.',
            'message.max' => 'Message cannot exceed 5000 characters.',
            'attachments.max' => 'You can attach up to 3 images.',
            'attachments.*.image' => 'Attachments must be images.',
            'attachments.*.max' => 'Each image must be under 5MB.',
            'attachments.*.mimes' => 'Allowed image formats: JPG, PNG, GIF.',
        ];
    }
}
