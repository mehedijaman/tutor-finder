<?php

use App\Enums\TaxonomyStatus;
use App\Models\Area;
use App\Models\Category;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionType;
use App\Models\TutorProfile;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('applies extended tutor filters on the public tutor directory', function () {
    $wantedTuitionType = TuitionType::factory()->create([
        'name' => 'Home Tutoring',
        'status' => TaxonomyStatus::Active,
    ]);
    $otherTuitionType = TuitionType::factory()->create([
        'name' => 'Online Tutoring',
        'status' => TaxonomyStatus::Active,
    ]);

    $wantedCategory = Category::factory()->create([
        'name' => 'English Version',
        'status' => TaxonomyStatus::Active,
    ]);
    $otherCategory = Category::factory()->create([
        'name' => 'Bangla Version',
        'status' => TaxonomyStatus::Active,
    ]);

    $wantedClass = SchoolClass::factory()->create([
        'category_id' => $wantedCategory->id,
        'status' => TaxonomyStatus::Active,
    ]);
    $otherClass = SchoolClass::factory()->create([
        'category_id' => $otherCategory->id,
        'status' => TaxonomyStatus::Active,
    ]);

    $wantedSubject = Subject::factory()->create([
        'class_id' => $wantedClass->id,
        'status' => TaxonomyStatus::Active,
    ]);
    $otherSubject = Subject::factory()->create([
        'class_id' => $otherClass->id,
        'status' => TaxonomyStatus::Active,
    ]);

    $wantedArea = Area::factory()->create([
        'status' => TaxonomyStatus::Active,
    ]);
    $otherArea = Area::factory()->create([
        'status' => TaxonomyStatus::Active,
    ]);

    $matchingTutor = User::factory()->tutor()->create([
        'verified_at' => now(),
    ]);

    TutorProfile::factory()->create([
        'user_id' => $matchingTutor->id,
        'gender' => 'female',
        'present_address' => 'Dhanmondi, Dhaka',
        'preferred_tuition_types' => [$wantedTuitionType->id],
        'preferred_categories' => [$wantedCategory->id],
        'preferred_classes' => [$wantedClass->id],
        'preferred_subjects' => [$wantedSubject->id],
        'preferred_locations' => [$wantedArea->id],
        'expected_salary_min' => 8000,
        'expected_salary_max' => 12000,
        'available_days' => ['sat'],
    ]);

    $otherTutor = User::factory()->tutor()->create();

    TutorProfile::factory()->create([
        'user_id' => $otherTutor->id,
        'gender' => 'male',
        'present_address' => 'Uttara, Dhaka',
        'preferred_tuition_types' => [$otherTuitionType->id],
        'preferred_categories' => [$otherCategory->id],
        'preferred_classes' => [$otherClass->id],
        'preferred_subjects' => [$otherSubject->id],
        'preferred_locations' => [$otherArea->id],
        'expected_salary_min' => 4000,
        'expected_salary_max' => 6000,
        'available_days' => ['fri'],
    ]);

    $this->get(route('tutors', [
        'gender' => 'female',
        'tuition_type_id' => $wantedTuitionType->id,
        'category_id' => $wantedCategory->id,
        'class_id' => $wantedClass->id,
        'subject_id' => $wantedSubject->id,
        'location_id' => $wantedArea->id,
        'available_day' => 'sat',
        'min_budget' => 7000,
        'max_budget' => 13000,
        'verified' => 'yes',
    ]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tutors')
            ->has('tutors.data', 1)
            ->where('tutors.data.0.id', $matchingTutor->id)
            ->where('filters.verified', 'yes')
            ->where('filters.available_day', 'sat'));
});

it('returns mapped tutor preference labels and filter options for listing and profile pages', function () {
    $tuitionType = TuitionType::factory()->create([
        'name' => 'Online Support',
        'status' => TaxonomyStatus::Active,
    ]);
    $category = Category::factory()->create([
        'name' => 'O Level',
        'status' => TaxonomyStatus::Active,
    ]);
    $schoolClass = SchoolClass::factory()->create([
        'name' => 'Class 9',
        'category_id' => $category->id,
        'status' => TaxonomyStatus::Active,
    ]);
    $subject = Subject::factory()->create([
        'name' => 'Physics',
        'class_id' => $schoolClass->id,
        'status' => TaxonomyStatus::Active,
    ]);
    $area = Area::factory()->create([
        'name' => 'Mirpur DOHS',
        'status' => TaxonomyStatus::Active,
    ]);

    $tutor = User::factory()->tutor()->create();

    TutorProfile::factory()->create([
        'user_id' => $tutor->id,
        'preferred_tuition_types' => [$tuitionType->id],
        'preferred_categories' => [$category->id],
        'preferred_classes' => [$schoolClass->id],
        'preferred_subjects' => [$subject->id],
        'preferred_locations' => [$area->id],
    ]);

    $this->get(route('tutors'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tutors')
            ->where('filterOptions.tuitionTypes.0.id', $tuitionType->id)
            ->where('filterOptions.tuitionTypes.0.name', 'Online Support')
            ->where('tutors.data.0.tutor_profile.preferred_tuition_types.0', 'Online Support')
            ->where('tutors.data.0.tutor_profile.preferred_categories.0', 'O Level')
            ->where('tutors.data.0.tutor_profile.preferred_classes.0', 'Class 9')
            ->where('tutors.data.0.tutor_profile.preferred_subjects.0', 'Physics')
            ->where('tutors.data.0.tutor_profile.preferred_locations.0', 'Mirpur DOHS'));

    $this->get(route('tutors.show', $tutor->id))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('TutorShow')
            ->where('tutor.tutor_profile.preferred_tuition_types.0', 'Online Support')
            ->where('tutor.tutor_profile.preferred_categories.0', 'O Level')
            ->where('tutor.tutor_profile.preferred_classes.0', 'Class 9')
            ->where('tutor.tutor_profile.preferred_subjects.0', 'Physics')
            ->where('tutor.tutor_profile.preferred_locations.0', 'Mirpur DOHS'));
});
