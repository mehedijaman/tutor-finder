<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogPostStoreRequest extends FormRequest
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
            'status' => strtolower(trim((string) $this->input('status', 'draft'))),
            'slug' => trim((string) $this->input('slug')),
            'remove_cover' => $this->boolean('remove_cover'),
            'meta_title' => $this->nullableTrimmedString($this->input('meta_title')),
            'meta_description' => $this->nullableTrimmedString($this->input('meta_description')),
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blog_posts', 'slug')],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'cover' => ['nullable', 'image', 'max:4096'],
            'remove_cover' => ['required', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => [
                'integer',
                Rule::exists('blog_categories', 'id')
                    ->whereNull('deleted_at')
                    ->where('status', 'active'),
            ],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => [
                'integer',
                Rule::exists('blog_tags', 'id')
                    ->whereNull('deleted_at')
                    ->where('status', 'active'),
            ],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function nullableTrimmedString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
