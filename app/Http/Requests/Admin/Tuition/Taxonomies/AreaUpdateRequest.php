<?php

namespace App\Http\Requests\Admin\Tuition\Taxonomies;

use App\Models\Area;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AreaUpdateRequest extends FormRequest
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
            'city_id' => (int) $this->input('city_id'),
            'name' => trim((string) $this->input('name')),
            'slug' => trim((string) $this->input('slug')),
            'status' => strtolower(trim((string) $this->input('status', Area::STATUS_ACTIVE))),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Area|null $area */
        $area = $this->route('area');
        $cityId = (int) $this->input('city_id');

        return [
            'city_id' => [
                'required',
                'integer',
                Rule::exists('cities', 'id')->where(fn (Builder $query): Builder => $query->whereNull('deleted_at')),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas', 'name')
                    ->where(fn (Builder $query): Builder => $query->where('city_id', $cityId))
                    ->ignore($area),
            ],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in([Area::STATUS_ACTIVE, Area::STATUS_INACTIVE])],
        ];
    }
}
