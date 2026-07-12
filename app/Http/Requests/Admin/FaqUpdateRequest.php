<?php

namespace App\Http\Requests\Admin;

use App\Enums\FaqAudience;
use App\Enums\FaqStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
            'audience' => strtolower(trim((string) $this->input('audience', FaqAudience::Both->value))),
            'status' => strtolower(trim((string) $this->input('status', FaqStatus::Active->value))),
            'sort_order' => $this->normalizeSortOrder($this->input('sort_order')),
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
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'audience' => [
                'required',
                'string',
                new Enum(FaqAudience::class),
            ],
            'status' => [
                'required',
                'string',
                new Enum(FaqStatus::class),
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
