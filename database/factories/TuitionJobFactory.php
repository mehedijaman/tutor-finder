<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\TuitionJob;
use App\Models\TuitionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TuitionJob>
 */
class TuitionJobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(5);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraphs(3, true),
            'tuition_type_id' => TuitionType::factory(),
            'category_id' => Category::factory(),
            'class_id' => SchoolClass::factory(),
            'country_id' => Country::factory(),
            'city_id' => City::factory(),
            'area_id' => Area::factory(),
            'guardian_id' => User::factory()->guardian(),
            'location' => fake()->streetAddress(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'student_gender' => TuitionJob::GENDER_ANY,
            'tutor_gender' => TuitionJob::GENDER_ANY,
            'tuition_days' => ['sun', 'tue', 'thu'],
            'days_per_week' => 3,
            'tuition_time' => '5:00 PM - 7:00 PM',
            'tuition_duration' => '3 months',
            'no_of_students' => 1,
            'salary_amount' => 10000,
            'salary_currency' => 'BDT',
            'salary_negotiable' => false,
            'status' => TuitionJob::STATUS_PENDING,
            'cancellation_reason' => null,
            'published_at' => null,
            'expires_at' => now()->addDays(30),
            'created_by' => null,
            'updated_by' => null,
            'confirmed_by' => null,
            'confirmed_at' => null,
            'view_count' => 0,
        ];
    }

    /**
     * Indicate the job is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => TuitionJob::STATUS_PENDING,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate the job is live.
     */
    public function live(): static
    {
        return $this->state(fn (): array => [
            'status' => TuitionJob::STATUS_LIVE,
            'published_at' => now()->subHour(),
        ]);
    }
}
