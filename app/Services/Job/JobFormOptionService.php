<?php

namespace App\Services\Job;

use App\Enums\JobGender;
use App\Enums\TaxonomyStatus;
use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionType;
use App\Models\User;

class JobFormOptionService
{
    /**
     * Get active guardians for job forms.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function activeGuardians(): array
    {
        return User::query()
            ->where('role', 'guardian')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $guardian): array => [
                'id' => $guardian->id,
                'name' => $guardian->name,
            ])
            ->all();
    }

    /**
     * Get active tuition types.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function activeTuitionTypes(): array
    {
        return TuitionType::query()
            ->where('status', TaxonomyStatus::Active->value)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (TuitionType $tuitionType): array => [
                'id' => $tuitionType->id,
                'name' => $tuitionType->name,
            ])
            ->all();
    }

    /**
     * Get active categories.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function activeCategories(): array
    {
        return Category::query()
            ->where('status', TaxonomyStatus::Active->value)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->all();
    }

    /**
     * Get active school classes.
     *
     * @return array<int, array{id: int, name: string, category_id: int}>
     */
    public function activeSchoolClasses(): array
    {
        return SchoolClass::query()
            ->where('status', TaxonomyStatus::Active->value)
            ->ordered()
            ->get(['id', 'name', 'category_id'])
            ->map(fn (SchoolClass $schoolClass): array => [
                'id' => $schoolClass->id,
                'name' => $schoolClass->name,
                'category_id' => $schoolClass->category_id,
            ])
            ->all();
    }

    /**
     * Get active countries.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function activeCountries(): array
    {
        return Country::query()
            ->where('status', TaxonomyStatus::Active->value)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (Country $country): array => [
                'id' => $country->id,
                'name' => $country->name,
            ])
            ->all();
    }

    /**
     * Get active cities.
     *
     * @return array<int, array{id: int, name: string, country_id: int}>
     */
    public function activeCities(): array
    {
        return City::query()
            ->where('status', TaxonomyStatus::Active->value)
            ->ordered()
            ->get(['id', 'name', 'country_id'])
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'name' => $city->name,
                'country_id' => $city->country_id,
            ])
            ->all();
    }

    /**
     * Get active areas.
     *
     * @return array<int, array{id: int, name: string, city_id: int}>
     */
    public function activeAreas(): array
    {
        return Area::query()
            ->where('status', TaxonomyStatus::Active->value)
            ->ordered()
            ->get(['id', 'name', 'city_id'])
            ->map(fn (Area $area): array => [
                'id' => $area->id,
                'name' => $area->name,
                'city_id' => $area->city_id,
            ])
            ->all();
    }

    /**
     * Get active subjects.
     *
     * @return array<int, array{id: int, name: string, class_id: int}>
     */
    public function activeSubjects(): array
    {
        return Subject::query()
            ->where('status', TaxonomyStatus::Active->value)
            ->ordered()
            ->get(['id', 'name', 'class_id'])
            ->map(fn (Subject $subject): array => [
                'id' => $subject->id,
                'name' => $subject->name,
                'class_id' => $subject->class_id,
            ])
            ->all();
    }

    /**
     * Get gender options.
     *
     * @return array<int, array{value: JobGender, label: string}>
     */
    public function genderOptions(): array
    {
        return [
            ['value' => JobGender::Any, 'label' => 'Any'],
            ['value' => JobGender::Male, 'label' => 'Male'],
            ['value' => JobGender::Female, 'label' => 'Female'],
        ];
    }

    /**
     * Get selectable tuition days.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function dayOptions(): array
    {
        return [
            ['value' => 'sun', 'label' => 'Sunday'],
            ['value' => 'mon', 'label' => 'Monday'],
            ['value' => 'tue', 'label' => 'Tuesday'],
            ['value' => 'wed', 'label' => 'Wednesday'],
            ['value' => 'thu', 'label' => 'Thursday'],
            ['value' => 'fri', 'label' => 'Friday'],
            ['value' => 'sat', 'label' => 'Saturday'],
        ];
    }
}
