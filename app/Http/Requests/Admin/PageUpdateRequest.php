<?php

namespace App\Http\Requests\Admin;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class PageUpdateRequest extends FormRequest
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
            'title' => trim((string) $this->input('title')),
            'slug' => $this->normalizeSlug($this->input('slug'), $this->input('title')),
            'content' => trim((string) $this->input('content')),
            'status' => strtolower(trim((string) $this->input('status', PageStatus::Active->value))),
            'meta_title' => $this->trimOrNull($this->input('meta_title')),
            'meta_description' => $this->trimOrNull($this->input('meta_description')),
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique(Page::class, 'slug')->ignore($this->route('page'))],
            'content' => ['nullable', 'string'],
            'status' => ['required', 'string', new Enum(PageStatus::class)],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'remove_featured_image' => ['nullable', 'boolean'],
        ];
    }

    private function normalizeSlug(mixed $slug, mixed $title): string
    {
        $slug = trim((string) $slug);

        if ($slug === '') {
            return Str::slug(trim((string) $title));
        }

        return Str::slug($slug);
    }

    private function trimOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
