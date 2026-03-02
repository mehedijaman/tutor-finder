<?php

use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionJob;
use App\Models\TuitionType;
use App\Models\User;
use Database\Seeders\BangladeshDemoDataSeeder;

it('seeds bangladesh-focused taxonomy users and tuition jobs', function () {
    $this->seed(BangladeshDemoDataSeeder::class);

    expect(Country::query()->where('name', 'Bangladesh')->exists())->toBeTrue();

    expect(
        City::query()->whereIn('name', ['Dhaka', 'Chattogram', 'Rajshahi', 'Khulna', 'Sylhet'])->count()
    )->toBeGreaterThanOrEqual(5);

    expect(
        Area::query()->whereIn('name', ['Dhanmondi', 'Mirpur', 'Uttara', 'GEC Circle', 'Boalia'])->count()
    )->toBeGreaterThanOrEqual(5);

    expect(
        Category::query()->whereIn('name', ['Bangla Medium', 'English Medium', 'University Admission'])->count()
    )->toBe(3);

    expect(SchoolClass::query()->where('name', 'Class 8')->exists())->toBeTrue();
    expect(Subject::query()->where('name', 'Mathematics')->exists())->toBeTrue();

    expect(
        TuitionType::query()->whereIn('name', ['Home Tutoring', 'Online Tutoring', 'Group Tutoring'])->count()
    )->toBe(3);

    expect(
        User::query()->where('role', 'guardian')->where('email', 'like', '%.guardian@example.com')->count()
    )->toBe(8);

    expect(
        User::query()->where('role', 'tutor')->where('email', 'like', '%.tutor@example.com')->count()
    )->toBe(8);

    $job = TuitionJob::query()
        ->with(['guardian', 'city', 'subjects'])
        ->where('title', 'Need Class 8 Math and English Tutor in Dhanmondi')
        ->first();

    expect($job)->not->toBeNull();
    expect($job?->guardian?->role)->toBe('guardian');
    expect($job?->city?->name)->toBe('Dhaka');
    expect($job?->subjects->pluck('name')->all())->toContain('Mathematics');

    TuitionJob::query()->get()->each(function (TuitionJob $tuitionJob): void {
        expect($tuitionJob->days_per_week)->toBe(count($tuitionJob->tuition_days ?? []));
    });
});

it('is idempotent when the demo seeder is executed multiple times', function () {
    $this->seed(BangladeshDemoDataSeeder::class);
    $this->seed(BangladeshDemoDataSeeder::class);

    expect(
        User::query()->where('role', 'guardian')->where('email', 'like', '%.guardian@example.com')->count()
    )->toBe(8);

    expect(
        User::query()->where('role', 'tutor')->where('email', 'like', '%.tutor@example.com')->count()
    )->toBe(8);

    expect(
        TuitionJob::query()->whereIn('title', [
            'Need Class 8 Math and English Tutor in Dhanmondi',
            'SSC Science Tutor for Mirpur Girls School Student',
            'O Level Chemistry and Physics Tutor at Bashundhara R/A',
            'A Level Pure Math Home Tutor Needed in Uttara Sector 7',
            'Medical Admission Biology Coach for Chattogram GEC Area',
            'Engineering Admission Tutor in Rajshahi Boalia',
            'CSE First Year Programming Tutor in Sylhet Upashahar',
            'Dakhil English and Math Tutor in Khulna Sonadanga',
            'University A Unit Group Tuition in Mohammadpur',
            'Online Grade 5 English and Science Tutor for Evening Sessions',
        ])->count()
    )->toBe(10);
});
