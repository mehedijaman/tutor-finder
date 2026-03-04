<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a new user in pending verification state.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'role' => ['required', Rule::in(['guardian', 'tutor'])],
            'password' => $this->passwordRules(),
        ], [
            'role.in' => 'Invalid role selected.',
        ])->validate();

        return User::create([
            'name' => (string) $input['name'],
            'email' => isset($input['email']) && $input['email'] !== '' ? (string) $input['email'] : null,
            'phone' => (string) $input['phone'],
            'password' => Hash::make((string) $input['password']),
            'role' => (string) $input['role'],
            'status' => UserStatus::PendingVerification,
            'phone_verified_at' => null,
        ]);
    }
}
