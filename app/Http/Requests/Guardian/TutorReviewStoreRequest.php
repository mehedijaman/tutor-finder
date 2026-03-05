<?php

namespace App\Http\Requests\Guardian;

use App\Models\TuitionJobAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TutorReviewStoreRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'job_assignment_id' => [
                'required',
                'integer',
                Rule::exists('tuition_job_assignments', 'id')->whereNull('deleted_at'),
                Rule::unique('tutor_reviews', 'job_assignment_id')->whereNull('deleted_at'),
            ],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'job_assignment_id.exists' => 'The selected assignment does not exist.',
            'job_assignment_id.unique' => 'You have already reviewed this assignment.',
            'rating.min' => 'Rating must be at least 1 star.',
            'rating.max' => 'Rating cannot exceed 5 stars.',
            'comment.max' => 'Review comment cannot exceed 2000 characters.',
        ];
    }

    /**
     * Additional validation to ensure the guardian owns the job assignment.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $assignment = TuitionJobAssignment::query()
                ->with('job')
                ->find($this->input('job_assignment_id'));

            if (! $assignment || ! $assignment->job) {
                $validator->errors()->add('job_assignment_id', 'Invalid assignment.');

                return;
            }

            if ($assignment->job->guardian_id !== $this->user()->id) {
                $validator->errors()->add('job_assignment_id', 'You are not authorized to review this assignment.');
            }

            if (! $assignment->confirmed_at) {
                $validator->errors()->add('job_assignment_id', 'You can only review confirmed assignments.');
            }
        });
    }
}
