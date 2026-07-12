<?php

namespace App\Http\Requests\Admin;

use App\Models\BlogTag;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogTagUpdateRequest extends FormRequest
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
            'status' => strtolower(trim((string) $this->input('status', 'active'))),
            'slug' => trim((string) $this->input('slug')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var BlogTag|null $blogTag */
        $blogTag = $this->route('blogTag');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blog_tags', 'slug')->ignore($blogTag)],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
