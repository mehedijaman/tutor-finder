<?php

namespace App\Http\Requests;

use App\Enums\TaxonomyStatus;
use App\Models\Testimonial;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTestimonialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', Testimonial::class);
    }

    /**
     * Prepare incoming data before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->normalizeNullableInteger($this->input('user_id')),
            'name' => trim((string) $this->input('name')),
            'role' => $this->trimOrNull($this->input('role')),
            'avatar_url' => $this->trimOrNull($this->input('avatar_url')),
            'content' => trim((string) $this->input('content')),
            'status' => strtolower(trim((string) $this->input('status', TaxonomyStatus::Active->value))),
            'sort_order' => $this->normalizeNullableInteger($this->input('sort_order')),
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
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:50'],
            'avatar_url' => ['nullable', 'url', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'content' => ['required', 'string', 'max:1000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'status' => ['required', 'string', new Enum(TaxonomyStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function trimOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function normalizeNullableInteger(mixed $value): int|string|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }
}
