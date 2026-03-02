<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BlogImageUploadRequest extends FormRequest
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
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:'.$this->maxImageSizeKb(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $size = max(1, (int) config('blog.editor.max_image_size_mb', 5));

        return [
            'image.max' => "The image may not be greater than {$size} MB.",
        ];
    }

    protected function maxImageSizeKb(): int
    {
        $sizeInMb = max(1, (int) config('blog.editor.max_image_size_mb', 5));

        return $sizeInMb * 1024;
    }
}
