<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\ProfileStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\TutorProfileUpdateRequest;
use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionType;
use App\Models\TutorEducation;
use App\Models\TutorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class TutorProfileController extends Controller
{
    /**
     * Show tutor profile edit page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        $profile = $user->tutorProfile()->first();
        $educations = $user->tutorEducations()->get();

        return inertia('tutor/Profile/Edit', [
            'profile' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'gender' => $profile?->gender,
                'date_of_birth' => $profile?->date_of_birth?->toDateString(),
                'present_address' => $profile?->present_address,
                'permanent_address' => $profile?->permanent_address,
                'nid_no' => $profile?->nid_no,
                'bio' => $profile?->bio,
                'preferred_tuition_types' => $profile?->preferred_tuition_types ?? [],
                'preferred_categories' => $profile?->preferred_categories ?? [],
                'preferred_classes' => $profile?->preferred_classes ?? [],
                'preferred_subjects' => $profile?->preferred_subjects ?? [],
                'preferred_locations' => $profile?->preferred_locations ?? [],
                'expected_salary_min' => $profile?->expected_salary_min,
                'expected_salary_max' => $profile?->expected_salary_max,
                'available_days' => $profile?->available_days ?? [],
                'available_time' => $profile?->available_time,
                'status' => $profile?->status ?? ProfileStatus::Active,
                'educations' => $educations->map(fn (TutorEducation $education): array => [
                    'id' => $education->id,
                    'degree' => $education->degree,
                    'institute' => $education->institute,
                    'department' => $education->department,
                    'graduation_year' => $education->graduation_year,
                    'result' => $education->result,
                    'is_current' => $education->is_current,
                    'sort_order' => $education->sort_order,
                ])->values()->all(),
            ],
            'tuitionTypes' => $this->activeTuitionTypes(),
            'categories' => $this->activeCategories(),
            'schoolClasses' => $this->activeSchoolClasses(),
            'subjects' => $this->activeSubjects(),
            'locations' => $this->activeLocations(),
            'dayOptions' => $this->dayOptions(),
            'genderOptions' => [
                ['value' => 'male', 'label' => 'Male'],
                ['value' => 'female', 'label' => 'Female'],
                ['value' => 'other', 'label' => 'Other'],
                ['value' => 'prefer_not_to_say', 'label' => 'Prefer Not to Say'],
            ],
        ]);
    }

    /**
     * Update tutor profile and education records.
     */
    public function update(TutorProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        DB::transaction(function () use ($validated, $user): void {
            $user->forceFill([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
            ])->save();

            $profileData = Arr::except($validated, ['name', 'phone', 'educations']);

            $profile = TutorProfile::query()->firstOrNew([
                'user_id' => $user->getKey(),
            ]);

            $profile->fill($profileData);
            $profile->status = $profileData['status'] ?? ProfileStatus::Active;
            $profile->save();

            $this->syncEducations($user->getKey(), $validated['educations'] ?? []);
        });

        return redirect()
            ->route('tutor.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }

    /**
     * Sync tutor education records.
     *
     * @param  array<int, array<string, mixed>>  $educations
     */
    private function syncEducations(int $userId, array $educations): void
    {
        $submittedIds = collect($educations)
            ->pluck('id')
            ->filter()
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        TutorEducation::query()
            ->where('user_id', $userId)
            ->when($submittedIds !== [], fn ($query) => $query->whereNotIn('id', $submittedIds))
            ->when($submittedIds === [], fn ($query) => $query)
            ->get()
            ->each(fn (TutorEducation $education) => $education->delete());

        foreach ($educations as $index => $payload) {
            /** @var TutorEducation $education */
            $education = isset($payload['id']) && $payload['id']
                ? TutorEducation::query()
                    ->withTrashed()
                    ->where('user_id', $userId)
                    ->whereKey($payload['id'])
                    ->firstOrFail()
                : new TutorEducation;

            if ($education->trashed()) {
                $education->restore();
            }

            $education->forceFill([
                'user_id' => $userId,
                'degree' => (string) $payload['degree'],
                'institute' => (string) $payload['institute'],
                'department' => $payload['department'] ?? null,
                'graduation_year' => $payload['graduation_year'] ?? null,
                'result' => $payload['result'] ?? null,
                'is_current' => (bool) ($payload['is_current'] ?? false),
                'sort_order' => isset($payload['sort_order']) ? (int) $payload['sort_order'] : $index,
            ])->save();
        }
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function activeTuitionTypes(): array
    {
        return TuitionType::query()
            ->where('status', TuitionType::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (TuitionType $item): array => [
                'id' => $item->id,
                'name' => $item->name,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function activeCategories(): array
    {
        return Category::query()
            ->where('status', Category::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (Category $item): array => [
                'id' => $item->id,
                'name' => $item->name,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, category_id: int}>
     */
    private function activeSchoolClasses(): array
    {
        return SchoolClass::query()
            ->where('status', SchoolClass::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name', 'category_id'])
            ->map(fn (SchoolClass $item): array => [
                'id' => $item->id,
                'name' => $item->name,
                'category_id' => $item->category_id,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, class_id: int}>
     */
    private function activeSubjects(): array
    {
        return Subject::query()
            ->where('status', Subject::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name', 'class_id'])
            ->map(fn (Subject $item): array => [
                'id' => $item->id,
                'name' => $item->name,
                'class_id' => $item->class_id,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, city_id: int|null}>
     */
    private function activeLocations(): array
    {
        $cityLocations = City::query()
            ->where('status', City::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'name' => $city->name,
                'city_id' => null,
            ]);

        $areaLocations = Area::query()
            ->where('status', Area::STATUS_ACTIVE)
            ->ordered()
            ->get(['id', 'name', 'city_id'])
            ->map(fn (Area $area): array => [
                'id' => $area->id,
                'name' => $area->name,
                'city_id' => $area->city_id,
            ]);

        return $cityLocations
            ->concat($areaLocations)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function dayOptions(): array
    {
        return [
            ['value' => 'sat', 'label' => 'Saturday'],
            ['value' => 'sun', 'label' => 'Sunday'],
            ['value' => 'mon', 'label' => 'Monday'],
            ['value' => 'tue', 'label' => 'Tuesday'],
            ['value' => 'wed', 'label' => 'Wednesday'],
            ['value' => 'thu', 'label' => 'Thursday'],
            ['value' => 'fri', 'label' => 'Friday'],
        ];
    }
}
