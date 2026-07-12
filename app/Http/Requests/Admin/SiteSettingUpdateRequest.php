<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SiteSettingUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare incoming request data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'remove_logo' => $this->boolean('remove_logo'),
            'remove_favicon' => $this->boolean('remove_favicon'),
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
            'site_name' => ['required', 'string', 'max:255'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['required', 'boolean'],
            'favicon' => ['nullable', 'image', 'mimes:ico,png,jpg,jpeg,gif,svg', 'max:512'],
            'remove_favicon' => ['required', 'boolean'],
            'phone_numbers' => ['nullable', 'array'],
            'phone_numbers.*' => ['nullable', 'string', 'max:50'],
            'emails' => ['nullable', 'array'],
            'emails.*' => ['nullable', 'email', 'max:255'],
            'addresses' => ['nullable', 'array'],
            'addresses.*.label' => ['nullable', 'string', 'max:120'],
            'addresses.*.address' => ['nullable', 'string', 'max:500'],
            'addresses.*.map_url' => ['nullable', 'url', 'max:2048'],
            'social_details' => ['nullable', 'array'],
            'social_details.*.platform' => ['nullable', 'string', 'max:120'],
            'social_details.*.url' => ['nullable', 'url', 'max:2048'],
            'trade_licence_no' => ['nullable', 'string', 'max:120'],
            'tin_no' => ['nullable', 'string', 'max:120'],
            'bin_no' => ['nullable', 'string', 'max:120'],
        ];
    }
}
