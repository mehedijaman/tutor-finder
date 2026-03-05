<?php

namespace App\Http\Requests\Admin;

use App\Enums\TutorialAudience;
use App\Models\Tutorial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class TutorialStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'slug' => $this->normalizeSlug($this->input('slug'), $this->input('title')),
            'video_url' => trim((string) $this->input('video_url')),
            'audience' => strtolower(trim((string) $this->input('audience', TutorialAudience::All->value))),
            'description' => $this->trimOrNull($this->input('description')),
            'sort_order' => (int) $this->input('sort_order', 0),
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:'.Tutorial::class.',slug'],
            'video_url' => ['required', 'url', 'max:500'],
            'audience' => ['required', 'string', new Enum(TutorialAudience::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
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
