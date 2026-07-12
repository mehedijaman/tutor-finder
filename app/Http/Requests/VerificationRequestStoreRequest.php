<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Enums\VerificationRole;
use App\Enums\VerificationStatus;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerificationRequestStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Configure additional validation checks.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();

            if (! $user instanceof User) {
                return;
            }

            $expectedRole = $this->routeIs('tutor.*') ? UserRole::Tutor : UserRole::Guardian;

            if ($user->role !== $expectedRole) {
                $validator->errors()->add('role', 'You are not authorized to submit verification request from this panel.');
            }

            if ($user->verification_status === VerificationStatus::Verified || $user->verified_at !== null) {
                $validator->errors()->add('verification', 'Your profile is already verified.');
            }

            $hasActiveRequest = VerificationRequest::query()
                ->where('user_id', $user->getKey())
                ->whereIn('status', VerificationRequest::activeStatuses())
                ->exists();

            if ($hasActiveRequest) {
                $validator->errors()->add('verification', 'An active verification request already exists.');
            }
        });
    }

    /**
     * Resolve role based on route namespace.
     */
    public function requestedRole(): string
    {
        return $this->routeIs('tutor.*')
            ? VerificationRole::Tutor->value
            : VerificationRole::Guardian->value;
    }
}
