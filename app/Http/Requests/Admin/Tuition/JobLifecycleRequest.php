<?php

namespace App\Http\Requests\Admin\Tuition;

use Illuminate\Foundation\Http\FormRequest;

class JobLifecycleRequest extends FormRequest
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
            'reason' => trim((string) $this->input('reason')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->routeIs('admin.tuition.jobs.cancel')) {
            return [
                'reason' => ['nullable', 'string', 'max:5000'],
            ];
        }

        return [
            'reason' => ['prohibited'],
        ];
    }
}
