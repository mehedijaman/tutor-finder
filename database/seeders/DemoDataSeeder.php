<?php

namespace Database\Seeders;

use App\Enums\JobGender;
use App\Enums\JobStatus;
use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionJob;
use App\Models\TuitionType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding countries, cities, areas, categories, classes, subjects, tuition types...');
        $this->call([
            BangladeshTuitionTaxonomySeeder::class,
        ]);

        $this->command->info('Creating 2000 tutors...');
        User::factory()
            ->tutor()
            ->count(2000)
            ->sequence(fn ($sequence) => [
                'email' => 'tutor'.($sequence->index + 100).'@example.com',
                'phone' => '+8801'.sprintf('%09d', $sequence->index + 100000000),
            ])
            ->create();

        $this->command->info('Creating 2000 guardians...');
        User::factory()
            ->guardian()
            ->count(2000)
            ->sequence(fn ($sequence) => [
                'email' => 'guardian'.($sequence->index + 100).'@example.com',
                'phone' => '+8801'.sprintf('%09d', $sequence->index + 300000000),
            ])
            ->create();

        $this->command->info('Creating 2000 tuition jobs...');
        $this->seedJobs();
    }

    private function seedJobs(): void
    {
        $guardians = User::query()->where('role', 'guardian')->get();

        $categories = Category::query()->get();
        $classes = SchoolClass::query()->get();
        $subjects = Subject::query()->get();
        $cities = City::query()->get();
        $areas = Area::query()->get();
        $tuitionTypes = TuitionType::query()->get();

        $statuses = [
            JobStatus::Pending,
            JobStatus::Live,
            JobStatus::Live,
            JobStatus::Live,
            JobStatus::Confirmed,
            JobStatus::Cancelled,
            JobStatus::Closed,
        ];

        $genders = [
            JobGender::Any,
            JobGender::Male,
            JobGender::Female,
        ];

        $daysOptions = [
            ['sun', 'tue', 'thu'],
            ['mon', 'wed', 'fri'],
            ['sat', 'sun', 'tue'],
            ['sun', 'tue', 'thu', 'sat'],
            ['mon', 'wed'],
            ['sun', 'tue'],
        ];

        $times = [
            '4:00 PM - 6:00 PM',
            '5:00 PM - 7:00 PM',
            '6:00 PM - 8:00 PM',
            '7:00 PM - 9:00 PM',
            '8:00 PM - 10:00 PM',
            '3:00 PM - 5:00 PM',
            '9:00 AM - 11:00 AM',
            '10:00 AM - 12:00 PM',
        ];

        $durations = [
            '1 month',
            '2 months',
            '3 months',
            '4 months',
            '5 months',
            '6 months',
            '8 months',
            '1 year',
        ];

        $salaries = [5000, 6000, 7000, 8000, 9000, 10000, 12000, 15000, 18000, 20000, 25000, 30000];

        $titles = [
            'Need {subject} Tutor in {area}',
            'Looking for {subject} Teacher for {class} in {city}',
            '{class} {subject} Home Tutor Required',
            'Urgent: {subject} Tutor Needed in {area}',
            '{class} Student needs {subject} Tutor',
            'Looking for experienced {subject} Teacher',
            '{subject} Coaching Required for {class}',
            'Private {subject} Tutor for {class} Student',
            '{class} {subject} Teacher Wanted',
            'Expert {subject} Tutor for {class}',
        ];

        for ($i = 0; $i < 2000; $i++) {
            $guardian = $guardians->random();
            $category = $categories->random();
            $class = $classes->where('category_id', $category->id)->random();
            $subjectForClass = $subjects->where('class_id', $class->id)->take(3)->pluck('id')->toArray();

            if (empty($subjectForClass)) {
                $subjectForClass = [$subjects->random()->id];
            }

            $subjectNamesForJob = $subjects->whereIn('id', $subjectForClass)->pluck('name')->toArray();
            $subjectName = ! empty($subjectNamesForJob) ? implode(' and ', array_slice($subjectNamesForJob, 0, 2)) : 'Subject';

            $city = $cities->random();
            $area = $areas->where('city_id', $city->id)->random();
            $tuitionType = $tuitionTypes->random();

            $status = $statuses[array_rand($statuses)];
            $titleTemplate = $titles[array_rand($titles)];

            $title = str_replace(
                ['{subject}', '{class}', '{area}', '{city}'],
                [$subjectName, $class->name, $area->name, $city->name],
                $titleTemplate
            );

            $days = $daysOptions[array_rand($daysOptions)];
            $salary = $salaries[array_rand($salaries)];

            $job = TuitionJob::create([
                'title' => $title,
                'slug' => Str::slug($title).'-'.uniqid(),
                'description' => 'Looking for a qualified tutor for '.$class->name.' student. '.
                    'Subjects: '.implode(', ', $subjectNamesForJob).'. '.
                    'Location: '.$area->name.', '.$city->name.'.',
                'tuition_type_id' => $tuitionType->id,
                'category_id' => $category->id,
                'class_id' => $class->id,
                'country_id' => $city->country_id,
                'city_id' => $city->id,
                'area_id' => $area->id,
                'guardian_id' => $guardian->id,
                'location' => $area->name.', '.$city->name,
                'latitude' => $area->latitude,
                'longitude' => $area->longitude,
                'student_gender' => $genders[array_rand($genders)],
                'tutor_gender' => $genders[array_rand($genders)],
                'tuition_days' => $days,
                'days_per_week' => count($days),
                'tuition_time' => $times[array_rand($times)],
                'tuition_duration' => $durations[array_rand($durations)],
                'no_of_students' => rand(1, 5),
                'salary_amount' => $salary,
                'salary_currency' => 'BDT',
                'salary_negotiable' => (bool) rand(0, 1),
                'status' => $status,
                'published_at' => $status !== JobStatus::Pending ? now()->subDays(rand(0, 30)) : null,
                'expires_at' => in_array($status, [JobStatus::Cancelled, JobStatus::Closed])
                    ? now()->subDays(rand(1, 10))
                    : now()->addDays(rand(10, 60)),
                'view_count' => rand(0, 500),
            ]);

            $job->subjects()->sync($subjectForClass);
        }

        $this->command->info('Created 2000 tuition jobs.');
    }
}
