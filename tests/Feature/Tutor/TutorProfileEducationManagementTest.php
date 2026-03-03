<?php

use App\Models\TutorEducation;
use App\Models\TutorProfile;
use App\Models\User;

it('tutor can update profile and synchronize education records', function () {
    $tutor = User::factory()->tutor()->create();

    $profile = TutorProfile::factory()->create([
        'user_id' => $tutor->id,
    ]);

    $firstEducation = TutorEducation::factory()->create([
        'user_id' => $tutor->id,
        'degree' => 'BSc in Mathematics',
    ]);

    $secondEducation = TutorEducation::factory()->create([
        'user_id' => $tutor->id,
        'degree' => 'MSc in Physics',
    ]);

    $response = $this->actingAs($tutor)->put(route('tutor.profile.update'), [
        'name' => 'Updated Tutor Name',
        'phone' => '+8801712345678',
        'gender' => 'male',
        'present_address' => 'Dhaka',
        'permanent_address' => 'Khulna',
        'nid_no' => '1234567890',
        'bio' => 'Experienced tutor',
        'preferred_tuition_types' => [1, 2],
        'preferred_categories' => [3],
        'preferred_classes' => [4, 5],
        'preferred_subjects' => [6],
        'preferred_locations' => [7],
        'expected_salary_min' => 5000,
        'expected_salary_max' => 10000,
        'available_days' => ['sat', 'mon'],
        'available_time' => '4 PM - 8 PM',
        'status' => 'active',
        'educations' => [
            [
                'id' => $firstEducation->id,
                'degree' => 'BSc in CSE',
                'institute' => 'University A',
                'department' => 'Computer Science',
                'graduation_year' => 2020,
                'result' => '3.80/4.00',
                'is_current' => false,
                'sort_order' => 0,
            ],
            [
                'degree' => 'MSc in CSE',
                'institute' => 'University B',
                'department' => 'Computer Science',
                'graduation_year' => 2022,
                'result' => '3.90/4.00',
                'is_current' => false,
                'sort_order' => 1,
            ],
        ],
    ]);

    $response->assertRedirect(route('tutor.profile.edit', absolute: false));

    expect($tutor->fresh()->name)->toBe('Updated Tutor Name');
    expect($tutor->fresh()->phone)->toBe('+8801712345678');

    expect($profile->fresh()->present_address)->toBe('Dhaka');
    expect($profile->fresh()->preferred_tuition_types)->toBe([1, 2]);

    expect(TutorEducation::query()->where('user_id', $tutor->id)->count())->toBe(2);

    $updatedFirst = TutorEducation::query()->findOrFail($firstEducation->id);

    expect($updatedFirst->degree)->toBe('BSc in CSE');

    expect(TutorEducation::withTrashed()->findOrFail($secondEducation->id)->trashed())->toBeTrue();

    expect(
        TutorEducation::query()
            ->where('user_id', $tutor->id)
            ->where('degree', 'MSc in CSE')
            ->exists()
    )->toBeTrue();
});

it('tutor profile update creates profile and education records when missing', function () {
    $tutor = User::factory()->tutor()->create();

    expect(TutorProfile::query()->where('user_id', $tutor->id)->exists())->toBeFalse();
    expect(TutorEducation::query()->where('user_id', $tutor->id)->exists())->toBeFalse();

    $response = $this->actingAs($tutor)->put(route('tutor.profile.update'), [
        'name' => 'New Tutor',
        'phone' => '+8801812345678',
        'gender' => 'female',
        'present_address' => 'Mirpur, Dhaka',
        'preferred_tuition_types' => [1],
        'preferred_categories' => [2],
        'preferred_classes' => [3],
        'preferred_subjects' => [4],
        'preferred_locations' => [5],
        'available_days' => ['sun', 'tue'],
        'status' => 'active',
        'educations' => [
            [
                'degree' => 'BSc in Chemistry',
                'institute' => 'National University',
                'department' => 'Chemistry',
                'graduation_year' => 2021,
                'result' => '3.60/4.00',
                'is_current' => false,
                'sort_order' => 0,
            ],
        ],
    ]);

    $response->assertRedirect(route('tutor.profile.edit', absolute: false));

    $profile = TutorProfile::query()->where('user_id', $tutor->id)->first();
    $education = TutorEducation::query()->where('user_id', $tutor->id)->first();

    expect($profile)->not->toBeNull();
    expect($profile?->present_address)->toBe('Mirpur, Dhaka');
    expect($profile?->preferred_tuition_types)->toBe([1]);

    expect($education)->not->toBeNull();
    expect($education?->degree)->toBe('BSc in Chemistry');
    expect($education?->institute)->toBe('National University');
});
