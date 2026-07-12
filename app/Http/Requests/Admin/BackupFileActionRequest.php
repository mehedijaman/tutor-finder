<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BackupFileActionRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'disk' => ['required', 'string', 'max:100'],
            'backup_name' => ['required', 'string', 'max:255'],
            'path' => ['required', 'string', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for backup file action validation.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'disk.required' => 'Backup disk is required.',
            'backup_name.required' => 'Backup name is required.',
            'path.required' => 'Backup file path is required.',
        ];
    }
}
