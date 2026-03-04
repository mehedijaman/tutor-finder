<?php

namespace Database\Seeders;

use App\Enums\DurationType;
use App\Enums\FeePaymentMode;
use App\Enums\JobGender;
use App\Enums\JobStatus;
use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionJob;
use App\Models\TuitionJobAssignment;
use App\Models\TuitionType;
use App\Models\User;
use App\Support\SlugService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use RuntimeException;

class BangladeshDemoJobsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            BangladeshTuitionTaxonomySeeder::class,
            BangladeshDemoUsersSeeder::class,
        ]);

        $slugService = app(SlugService::class);

        $country = Country::query()->where('name', 'Bangladesh')->firstOrFail();
        $admin = User::query()->where('role', 'admin')->where('status', 'active')->orderBy('id')->first();

        $citiesByName = City::query()
            ->where('country_id', $country->id)
            ->get()
            ->keyBy('name');

        $areasByCityAndName = Area::query()
            ->whereIn('city_id', $citiesByName->pluck('id'))
            ->get()
            ->groupBy('city_id')
            ->map(fn (Collection $areas): Collection => $areas->keyBy('name'));

        $categoriesByName = Category::query()->get()->keyBy('name');
        $classesByCategoryAndName = SchoolClass::query()
            ->get()
            ->groupBy('category_id')
            ->map(fn (Collection $schoolClasses): Collection => $schoolClasses->keyBy('name'));

        $subjectsByClassAndName = Subject::query()
            ->get()
            ->groupBy('class_id')
            ->map(fn (Collection $subjects): Collection => $subjects->keyBy('name'));

        $tuitionTypesByName = TuitionType::query()->get()->keyBy('name');

        $guardiansByEmail = User::query()
            ->where('role', 'guardian')
            ->where('status', 'active')
            ->get()
            ->keyBy('email');

        $tutorsByEmail = User::query()
            ->where('role', 'tutor')
            ->where('status', 'active')
            ->get()
            ->keyBy('email');

        $jobs = [
            [
                'title' => 'Need Class 8 Math and English Tutor in Dhanmondi',
                'guardian_email' => 'fatema.rahman.guardian@example.com',
                'tuition_type' => 'Home Tutoring',
                'category' => 'Bangla Medium',
                'class' => 'Class 8',
                'subjects' => ['Mathematics', 'English 1st Paper'],
                'city' => 'Dhaka',
                'area' => 'Dhanmondi',
                'location' => 'Road 8A, Dhanmondi, near Dhanmondi Lake',
                'latitude' => 23.7465,
                'longitude' => 90.3760,
                'student_gender' => JobGender::Female,
                'tutor_gender' => JobGender::Female,
                'tuition_days' => ['sun', 'tue', 'thu'],
                'tuition_time' => '5:00 PM - 7:00 PM',
                'tuition_duration' => '6 months',
                'no_of_students' => 1,
                'salary_amount' => 12000,
                'salary_negotiable' => true,
                'status' => JobStatus::Live,
                'published_days_ago' => 6,
                'expires_in_days' => 24,
                'view_count' => 184,
                'description' => 'Guardian is looking for an experienced female tutor for a Class 8 student of a reputed Bangla medium school. Strong focus required on Mathematics problem solving and English grammar practice.',
            ],
            [
                'title' => 'SSC Science Tutor for Mirpur Girls School Student',
                'guardian_email' => 'nusrat.jahan.guardian@example.com',
                'tuition_type' => 'Home Tutoring',
                'category' => 'Bangla Medium',
                'class' => 'SSC Science (Class 10)',
                'subjects' => ['Physics', 'Chemistry', 'Higher Mathematics'],
                'city' => 'Dhaka',
                'area' => 'Mirpur',
                'location' => 'Section 10, Mirpur, opposite local market',
                'latitude' => 23.8067,
                'longitude' => 90.3686,
                'student_gender' => JobGender::Female,
                'tutor_gender' => JobGender::Any,
                'tuition_days' => ['sat', 'mon', 'wed', 'thu'],
                'tuition_time' => '6:00 PM - 8:00 PM',
                'tuition_duration' => '8 months',
                'no_of_students' => 1,
                'salary_amount' => 15000,
                'salary_negotiable' => false,
                'status' => JobStatus::Live,
                'published_days_ago' => 10,
                'expires_in_days' => 20,
                'view_count' => 263,
                'description' => 'Looking for a science background tutor for SSC candidate with weekly test support. Student needs structured exam preparation for Physics, Chemistry, and Higher Math.',
            ],
            [
                'title' => 'O Level Chemistry and Physics Tutor at Bashundhara R/A',
                'guardian_email' => 'sabbir.ahmed.guardian@example.com',
                'tuition_type' => 'Home Tutoring',
                'category' => 'English Medium',
                'class' => 'O Level',
                'subjects' => ['Chemistry', 'Physics'],
                'city' => 'Dhaka',
                'area' => 'Bashundhara R/A',
                'location' => 'Block D, Bashundhara Residential Area',
                'latitude' => 23.8103,
                'longitude' => 90.4254,
                'student_gender' => JobGender::Male,
                'tutor_gender' => JobGender::Any,
                'tuition_days' => ['sun', 'tue', 'thu'],
                'tuition_time' => '7:00 PM - 9:00 PM',
                'tuition_duration' => '4 months',
                'no_of_students' => 1,
                'salary_amount' => 18000,
                'salary_negotiable' => false,
                'status' => JobStatus::Live,
                'published_days_ago' => 3,
                'expires_in_days' => 30,
                'view_count' => 142,
                'description' => 'Cambridge O Level student needs a tutor for Chemistry and Physics with prior experience in solving past papers. Preference for tutors from engineering or science universities.',
            ],
            [
                'title' => 'A Level Pure Math Home Tutor Needed in Uttara Sector 7',
                'guardian_email' => 'mahmud.hasan.guardian@example.com',
                'tuition_type' => 'Home Tutoring',
                'category' => 'English Medium',
                'class' => 'A Level',
                'subjects' => ['Pure Mathematics'],
                'city' => 'Dhaka',
                'area' => 'Uttara',
                'location' => 'House 19, Sector 7, Uttara',
                'latitude' => 23.8759,
                'longitude' => 90.3795,
                'student_gender' => JobGender::Male,
                'tutor_gender' => JobGender::Any,
                'tuition_days' => ['sat', 'mon', 'wed'],
                'tuition_time' => '8:00 PM - 9:30 PM',
                'tuition_duration' => '5 months',
                'no_of_students' => 1,
                'salary_amount' => 22000,
                'salary_negotiable' => true,
                'status' => JobStatus::Pending,
                'expires_in_days' => 18,
                'view_count' => 0,
                'description' => 'Guardian posted a pending request for A Level Pure Mathematics support. Student is preparing for upcoming assessment and needs twice-to-thrice weekly intensive sessions.',
            ],
            [
                'title' => 'Medical Admission Biology Coach for Chattogram GEC Area',
                'guardian_email' => 'farhana.akter.guardian@example.com',
                'tuition_type' => 'Coaching Batch',
                'category' => 'University Admission',
                'class' => 'Medical Admission',
                'subjects' => ['Biology', 'Chemistry', 'Physics', 'English'],
                'city' => 'Chattogram',
                'area' => 'GEC Circle',
                'location' => 'GEC Circle, near shopping complex',
                'latitude' => 22.3569,
                'longitude' => 91.8221,
                'student_gender' => JobGender::Female,
                'tutor_gender' => JobGender::Any,
                'tuition_days' => ['sun', 'mon', 'wed', 'thu'],
                'tuition_time' => '4:30 PM - 6:30 PM',
                'tuition_duration' => '7 months',
                'no_of_students' => 3,
                'salary_amount' => 20000,
                'salary_negotiable' => false,
                'status' => JobStatus::Live,
                'published_days_ago' => 5,
                'expires_in_days' => 40,
                'created_by_admin' => true,
                'view_count' => 198,
                'description' => 'Small medical admission batch needs a dedicated coach for Biology-led preparation with regular model test analysis. Guardian requested admin-assisted posting.',
            ],
            [
                'title' => 'Engineering Admission Tutor in Rajshahi Boalia',
                'guardian_email' => 'tanvir.hossain.guardian@example.com',
                'tuition_type' => 'Coaching Batch',
                'category' => 'University Admission',
                'class' => 'Engineering Admission',
                'subjects' => ['Physics', 'Chemistry', 'Higher Mathematics', 'ICT'],
                'city' => 'Rajshahi',
                'area' => 'Boalia',
                'location' => 'Boalia, close to Rajshahi College area',
                'latitude' => 24.3660,
                'longitude' => 88.6007,
                'student_gender' => JobGender::Male,
                'tutor_gender' => JobGender::Any,
                'tuition_days' => ['sat', 'mon', 'wed', 'fri'],
                'tuition_time' => '5:00 PM - 7:00 PM',
                'tuition_duration' => '6 months',
                'no_of_students' => 2,
                'salary_amount' => 17000,
                'salary_negotiable' => true,
                'status' => JobStatus::Pending,
                'expires_in_days' => 30,
                'view_count' => 0,
                'description' => 'Pending guardian request for BUET and engineering university admission preparation. Preference for tutors with previous admission coaching experience.',
            ],
            [
                'title' => 'CSE First Year Programming Tutor in Sylhet Upashahar',
                'guardian_email' => 'sadia.islam.guardian@example.com',
                'tuition_type' => 'Home Tutoring',
                'category' => 'University Undergraduate',
                'class' => 'CSE 1st Year',
                'subjects' => ['Structured Programming', 'Calculus', 'Discrete Mathematics'],
                'city' => 'Sylhet',
                'area' => 'Shahjalal Upashahar',
                'location' => 'Block B, Shahjalal Upashahar, Sylhet',
                'latitude' => 24.9025,
                'longitude' => 91.8687,
                'student_gender' => JobGender::Male,
                'tutor_gender' => JobGender::Any,
                'tuition_days' => ['sun', 'tue', 'thu'],
                'tuition_time' => '7:30 PM - 9:00 PM',
                'tuition_duration' => '4 months',
                'no_of_students' => 1,
                'salary_amount' => 16000,
                'salary_negotiable' => false,
                'status' => JobStatus::Confirmed,
                'published_days_ago' => 20,
                'expires_in_days' => 10,
                'confirmed_days_ago' => 7,
                'selected_tutor_email' => 'asif.rahman.tutor@example.com',
                'created_by_admin' => true,
                'view_count' => 210,
                'description' => 'University first-year student needed support in C programming and discrete mathematics. Tutor already finalized and job moved to confirmed state.',
            ],
            [
                'title' => 'Dakhil English and Math Tutor in Khulna Sonadanga',
                'guardian_email' => 'kamrul.hassan.guardian@example.com',
                'tuition_type' => 'Home Tutoring',
                'category' => 'Madrasah',
                'class' => 'Dakhil (Class 10)',
                'subjects' => ['English', 'Mathematics'],
                'city' => 'Khulna',
                'area' => 'Sonadanga',
                'location' => 'Sonadanga Residential Area, Khulna',
                'latitude' => 22.8456,
                'longitude' => 89.5403,
                'student_gender' => JobGender::Male,
                'tutor_gender' => JobGender::Any,
                'tuition_days' => ['sat', 'mon', 'wed'],
                'tuition_time' => '6:00 PM - 8:00 PM',
                'tuition_duration' => '3 months',
                'no_of_students' => 1,
                'salary_amount' => 9000,
                'salary_negotiable' => true,
                'status' => JobStatus::Cancelled,
                'published_days_ago' => 25,
                'cancellation_reason' => 'Student relocated to another city and no longer needs home tuition.',
                'view_count' => 95,
                'description' => 'A Dakhil candidate needed focused support in English and Mathematics. Job is now cancelled due to guardian relocation.',
            ],
            [
                'title' => 'University A Unit Group Tuition in Mohammadpur',
                'guardian_email' => 'fatema.rahman.guardian@example.com',
                'tuition_type' => 'Group Tutoring',
                'category' => 'University Admission',
                'class' => 'University A Unit',
                'subjects' => ['Bangla', 'English', 'General Knowledge'],
                'city' => 'Dhaka',
                'area' => 'Mohammadpur',
                'location' => 'Mohammadpur Housing, Dhaka',
                'latitude' => 23.7641,
                'longitude' => 90.3585,
                'student_gender' => JobGender::Any,
                'tutor_gender' => JobGender::Any,
                'tuition_days' => ['sat', 'sun', 'tue', 'thu'],
                'tuition_time' => '4:00 PM - 6:00 PM',
                'tuition_duration' => '5 months',
                'no_of_students' => 4,
                'salary_amount' => 7000,
                'salary_negotiable' => false,
                'status' => JobStatus::Closed,
                'published_days_ago' => 60,
                'created_by_admin' => true,
                'view_count' => 320,
                'description' => 'Group tuition posting for university A unit aspirants completed successfully after full admission cycle support.',
            ],
            [
                'title' => 'Online Grade 5 English and Science Tutor for Evening Sessions',
                'guardian_email' => 'mahmud.hasan.guardian@example.com',
                'tuition_type' => 'Online Tutoring',
                'category' => 'English Medium',
                'class' => 'Grade 5 (Cambridge)',
                'subjects' => ['English', 'Science', 'Mathematics'],
                'city' => 'Dhaka',
                'area' => 'Badda',
                'location' => 'Online classes from Badda via Zoom',
                'latitude' => null,
                'longitude' => null,
                'student_gender' => JobGender::Any,
                'tutor_gender' => JobGender::Any,
                'tuition_days' => ['sun', 'mon', 'wed'],
                'tuition_time' => '8:00 PM - 9:00 PM',
                'tuition_duration' => '3 months',
                'no_of_students' => 1,
                'salary_amount' => 8000,
                'salary_negotiable' => true,
                'status' => JobStatus::Live,
                'published_days_ago' => 2,
                'expires_in_days' => 21,
                'view_count' => 88,
                'description' => 'Guardian prefers an experienced online tutor for Cambridge Grade 5 English and Science with weekly worksheet follow-up and progress reports.',
            ],
        ];

        foreach ($jobs as $jobData) {
            $guardian = $guardiansByEmail->get($jobData['guardian_email']);

            if (! $guardian instanceof User) {
                throw new RuntimeException('Guardian not found for seeded job: '.$jobData['title']);
            }

            $tuitionType = $tuitionTypesByName->get($jobData['tuition_type']);

            if (! $tuitionType instanceof TuitionType) {
                throw new RuntimeException('Tuition type not found for seeded job: '.$jobData['title']);
            }

            $category = $categoriesByName->get($jobData['category']);

            if (! $category instanceof Category) {
                throw new RuntimeException('Category not found for seeded job: '.$jobData['title']);
            }

            $schoolClass = $classesByCategoryAndName->get($category->id)?->get($jobData['class']);

            if (! $schoolClass instanceof SchoolClass) {
                throw new RuntimeException('Class not found for seeded job: '.$jobData['title']);
            }

            $city = $citiesByName->get($jobData['city']);

            if (! $city instanceof City) {
                throw new RuntimeException('City not found for seeded job: '.$jobData['title']);
            }

            $area = null;

            if (($jobData['area'] ?? null) !== null) {
                $area = $areasByCityAndName->get($city->id)?->get($jobData['area']);

                if (! $area instanceof Area) {
                    throw new RuntimeException('Area not found for seeded job: '.$jobData['title']);
                }
            }

            $subjectIds = collect($jobData['subjects'])
                ->map(function (string $subjectName) use ($subjectsByClassAndName, $schoolClass, $jobData): int {
                    $subject = $subjectsByClassAndName->get($schoolClass->id)?->get($subjectName);

                    if (! $subject instanceof Subject) {
                        throw new RuntimeException("Subject {$subjectName} not found for seeded job: {$jobData['title']}");
                    }

                    return $subject->id;
                })
                ->values()
                ->all();

            $status = $jobData['status'];
            $tuitionDays = $this->normalizeTuitionDays($jobData['tuition_days'] ?? []);
            $selectedTutorId = null;

            if (($jobData['selected_tutor_email'] ?? null) !== null) {
                $selectedTutor = $tutorsByEmail->get($jobData['selected_tutor_email']);

                if (! $selectedTutor instanceof User) {
                    throw new RuntimeException('Tutor not found for seeded job: '.$jobData['title']);
                }

                $selectedTutorId = $selectedTutor->id;
            }

            $job = TuitionJob::query()->withTrashed()->firstOrNew([
                'title' => $jobData['title'],
                'guardian_id' => $guardian->id,
            ]);

            $job->fill([
                'description' => $jobData['description'],
                'tuition_type_id' => $tuitionType->id,
                'category_id' => $category->id,
                'class_id' => $schoolClass->id,
                'country_id' => $country->id,
                'city_id' => $city->id,
                'area_id' => $area?->id,
                'location' => $jobData['location'] ?? null,
                'latitude' => $jobData['latitude'] ?? null,
                'longitude' => $jobData['longitude'] ?? null,
                'student_gender' => $jobData['student_gender'] ?? JobGender::Any,
                'tutor_gender' => $jobData['tutor_gender'] ?? JobGender::Any,
                'tuition_days' => $tuitionDays,
                'days_per_week' => count($tuitionDays),
                'tuition_time' => $jobData['tuition_time'] ?? null,
                'tuition_duration' => $jobData['tuition_duration'] ?? null,
                'no_of_students' => $jobData['no_of_students'] ?? null,
                'salary_amount' => $jobData['salary_amount'] ?? null,
                'salary_currency' => 'BDT',
                'salary_negotiable' => (bool) ($jobData['salary_negotiable'] ?? false),
                'status' => $status,
                'cancellation_reason' => $status === JobStatus::Cancelled
                    ? ($jobData['cancellation_reason'] ?? 'Cancelled by guardian.')
                    : null,
                'published_at' => $this->resolvePublishedAt($status, $jobData['published_days_ago'] ?? null),
                'expires_at' => $this->resolveExpiresAt($status, $jobData['expires_in_days'] ?? null),
                'created_by' => ($jobData['created_by_admin'] ?? false) ? $admin?->id : null,
                'updated_by' => (($jobData['created_by_admin'] ?? false) || $status !== JobStatus::Pending) ? $admin?->id : null,
                'confirmed_by' => $status === JobStatus::Confirmed ? $admin?->id : null,
                'confirmed_at' => $status === JobStatus::Confirmed
                    ? now()->subDays((int) ($jobData['confirmed_days_ago'] ?? 5))
                    : null,
                'view_count' => (int) ($jobData['view_count'] ?? 0),
            ]);

            $job->slug = $slugService->unique(
                TuitionJob::class,
                $jobData['title'],
                $job->exists ? $job->getKey() : null,
            );

            $job->save();

            $this->restoreIfTrashed($job);

            $job->subjects()->sync($subjectIds);

            if ($status === JobStatus::Confirmed && $selectedTutorId !== null) {
                $confirmedAt = $job->confirmed_at ?? now();

                TuitionJobAssignment::query()->updateOrCreate(
                    ['job_id' => $job->id],
                    [
                        'tutor_user_id' => $selectedTutorId,
                        'appointed_at' => $confirmedAt,
                        'confirmed_at' => $confirmedAt,
                        'duration_type' => DurationType::LongTerm,
                        'fee_currency' => 'BDT',
                        'fee_payment_mode' => FeePaymentMode::PayBefore,
                        'month1_escrow_required' => false,
                        'reported_within_24h' => false,
                        'metadata' => ['seed' => 'BangladeshDemoJobsSeeder'],
                    ],
                );
            } else {
                TuitionJobAssignment::query()->where('job_id', $job->id)->delete();
            }
        }
    }

    /**
     * Normalize tuition days and remove invalid values.
     *
     * @param  array<int, string>  $days
     * @return array<int, string>
     */
    private function normalizeTuitionDays(array $days): array
    {
        $allowedDays = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

        return collect($days)
            ->map(fn (string $day): string => strtolower(trim($day)))
            ->filter(fn (string $day): bool => in_array($day, $allowedDays, true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Resolve published timestamp by lifecycle status.
     */
    private function resolvePublishedAt(JobStatus $status, ?int $daysAgo): ?\Carbon\CarbonInterface
    {
        if ($status === JobStatus::Pending) {
            return null;
        }

        return now()->subDays($daysAgo ?? 3);
    }

    /**
     * Resolve expiry timestamp by lifecycle status.
     */
    private function resolveExpiresAt(JobStatus $status, ?int $daysOffset): ?\Carbon\CarbonInterface
    {
        if ($status === JobStatus::Cancelled || $status === JobStatus::Closed) {
            return now()->subDays(abs($daysOffset ?? 5));
        }

        return now()->addDays(max(1, $daysOffset ?? 20));
    }

    /**
     * Restore a soft-deleted model instance when found in trashed state.
     */
    private function restoreIfTrashed(Model $model): void
    {
        if (method_exists($model, 'trashed') && $model->trashed()) {
            $model->restore();
        }
    }
}
