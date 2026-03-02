<?php

namespace App\Http\Requests\Admin;

use App\Models\Faq;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaqUpdateRequest extends FormRequest
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
            'question' => trim((string) $this->input('question')),
            'answer' => trim((string) $this->input('answer')),
            'audience' => strtolower(trim((string) $this->input('audience', Faq::AUDIENCE_BOTH))),
            'status' => strtolower(trim((string) $this->input('status', Faq::STATUS_ACTIVE))),
            'sort_order' => $this->normalizeSortOrder($this->input('sort_order')),
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
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'audience' => [
                'required',
                'string',
                Rule::in([Faq::AUDIENCE_TUTOR, Faq::AUDIENCE_GUARDIAN, Faq::AUDIENCE_BOTH]),
            ],
            'status' => [
                'required',
                'string',
                Rule::in([Faq::STATUS_ACTIVE, Faq::STATUS_INACTIVE]),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function normalizeSortOrder(mixed $value): int
    {
        if ($value === null || trim((string) $value) === '') {
            return 0;
        }

        return max(0, (int) $value);
    }
}
