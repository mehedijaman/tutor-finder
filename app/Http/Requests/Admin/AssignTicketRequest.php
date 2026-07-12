<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AssignTicketRequest extends FormRequest
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
            'assigned_to' => ['required', 'exists:users,id'],
        ];
    }

    /**
     * Additional validation after the primary rules pass.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $assignee = User::find($this->input('assigned_to'));

            if ($assignee && $assignee->role !== UserRole::Admin) {
                $validator->errors()->add('assigned_to', 'Tickets can only be assigned to admin users.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'assigned_to.required' => 'Please select an admin to assign.',
            'assigned_to.exists' => 'The selected user does not exist.',
        ];
    }
}
