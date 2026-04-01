<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProfileStatus;
use App\Enums\TaxonomyStatus;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManagedUserPasswordResetRequest;
use App\Http\Requests\Admin\ManagedUserStoreRequest;
use App\Http\Requests\Admin\ManagedUserUpdateRequest;
use App\Http\Requests\Admin\UserStatusUpdateRequest;
use App\Models\Area;
use App\Models\Category;
use App\Models\City;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TuitionType;
use App\Models\TutorEducation;
use App\Models\TutorProfile;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Response;

class TutorManagementController extends Controller
{
    /**
     * Map verification scope to concrete user statuses.
     *
     * @var array<string, list<string>>
     */
    private const VERIFICATION_SCOPES = [
        'pending' => [
            VerificationStatus::Pending,
            VerificationStatus::Approved,
            VerificationStatus::Invoiced,
        ],
        'unverified' => [VerificationStatus::Unverified],
        'verified' => [VerificationStatus::Verified],
    ];

    /**
     * Display tutors with filters.
     */
    public function index(Request $request): Response
    {
        $sort = $request->string('sort')->toString();

        if (! in_array($sort, ['name', 'phone', 'status', 'created_at'], true)) {
            $sort = 'created_at';
        }

        $direction = strtolower($request->string('direction')->toString());

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = $sort === 'created_at' ? 'desc' : 'asc';
        }

        $filters = [
            'search' => trim($request->string('search')->toString()),
            'status' => $request->string('status')->toString(),
            'verification' => strtolower(trim($request->string('verification')->toString())),
            'trash' => $request->boolean('trash'),
            'sort' => $sort,
            'direction' => $direction,
        ];

        if (! in_array($filters['verification'], ['pending', 'unverified', 'verified'], true)) {
            $filters['verification'] = 'all';
        }

        $items = User::query()
            ->where('role', UserRole::Tutor)
            ->with(['latestVerificationRequest.invoice'])
            ->when($filters['trash'], fn ($query) => $query->onlyTrashed())
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when(
                $filters['verification'] !== 'all',
                fn ($query) => $query->whereIn('verification_status', self::VERIFICATION_SCOPES[$filters['verification']]),
            )
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(function (User $user): array {
                $latestVerificationRequest = $user->latestVerificationRequest;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'verification_status' => $user->verification_status,
                    'verification_request_id' => $latestVerificationRequest?->id,
                    'verification_request_status' => $latestVerificationRequest?->status,
                    'verification_submitted_at' => $latestVerificationRequest?->submitted_at?->toDateTimeString(),
                    'verification_invoice_status' => $latestVerificationRequest?->invoice?->status,
                    'created_at' => $user->created_at?->toDateTimeString(),
                    'deleted_at' => $user->deleted_at?->toDateTimeString(),
                ];
            });

        return inertia('admin/tutors/Index', [
            'items' => $items,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the create tutor screen.
     */
    public function create(): Response
    {
        return inertia('admin/tutors/Create');
    }

    /**
     * Store a new tutor account.
     */
    public function store(ManagedUserStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'tutor',
            'status' => $validated['status'],
            'verification_status' => VerificationStatus::Unverified,
            'verification_type' => null,
            'verified_at' => null,
        ]);

        return redirect()->route('admin.tutors.index')->with('status', 'Tutor created successfully.');
    }

    /**
     * Display a tutor profile.
     */
    public function show(User $user): Response
    {
        if ($user->role !== UserRole::Tutor) {
            abort(404);
        }

        $user->load(['tutorProfile', 'tutorEducations', 'tutorReviews.guardian', 'tutorReviews.jobAssignment']);

        $profile = $user->tutorProfile;
        $educations = $user->tutorEducations;

        $verificationRequest = VerificationRequest::query()
            ->with('invoice')
            ->where('user_id', $user->getKey())
            ->latest('id')
            ->first();

        return inertia('admin/tutors/Show', [
            'tutor' => $user,
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'gender' => $profile?->gender ?? 'none',
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
                'profile_status' => $profile?->status ?? ProfileStatus::Active,
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
            'verification' => $verificationRequest ? [
                'id' => $verificationRequest->id,
                'status' => $verificationRequest->status,
                'role' => $verificationRequest->role,
                'fee_amount' => $verificationRequest->fee_amount,
                'currency' => $verificationRequest->currency,
                'submitted_at' => $verificationRequest->submitted_at?->toDateTimeString(),
                'reviewed_at' => $verificationRequest->reviewed_at?->toDateTimeString(),
                'decision_reason' => $verificationRequest->decision_reason,
                'invoice' => $verificationRequest->invoice ? [
                    'id' => $verificationRequest->invoice->id,
                    'invoice_no' => $verificationRequest->invoice->invoice_no,
                    'amount' => $verificationRequest->invoice->amount,
                    'currency' => $verificationRequest->invoice->currency,
                    'status' => $verificationRequest->invoice->status,
                    'due_at' => $verificationRequest->invoice->due_at?->toDateTimeString(),
                    'expires_at' => $verificationRequest->invoice->expires_at?->toDateTimeString(),
                    'paid_at' => $verificationRequest->invoice->paid_at?->toDateTimeString(),
                    'payment_gateway' => $verificationRequest->invoice->payment_gateway,
                ] : null,
            ] : null,
            'verificationStatus' => $user->verification_status,
            'verifiedAt' => $user->verified_at?->toDateTimeString(),
        ]);
    }

    /**
     * Show the tutor edit screen.
     */
    public function edit(User $user): Response
    {
        if ($user->role !== UserRole::Tutor) {
            abort(404);
        }

        return inertia('admin/tutors/Edit', [
            'tutor' => $user,
        ]);
    }

    /**
     * Update a tutor profile.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Tutor) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user)],
            'status' => ['required', 'string'],
            'gender' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'present_address' => ['nullable', 'string'],
            'permanent_address' => ['nullable', 'string'],
            'nid_no' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'preferred_tuition_types' => ['nullable', 'array'],
            'preferred_categories' => ['nullable', 'array'],
            'preferred_classes' => ['nullable', 'array'],
            'preferred_subjects' => ['nullable', 'array'],
            'preferred_locations' => ['nullable', 'array'],
            'expected_salary_min' => ['nullable', 'numeric'],
            'expected_salary_max' => ['nullable', 'numeric'],
            'available_days' => ['nullable', 'array'],
            'available_time' => ['nullable', 'string'],
            'profile_status' => ['nullable', 'string'],
            'educations' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($validated, $user): void {
            $user->forceFill([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ])->save();

            $profileData = Arr::except($validated, ['name', 'email', 'phone', 'status', 'educations', 'profile_status']);

            $profile = TutorProfile::query()->firstOrNew([
                'user_id' => $user->getKey(),
            ]);

            $profile->fill($profileData);
            $profile->gender = $validated['gender'] === 'none' ? null : $validated['gender'];
            $profile->status = $validated['profile_status'] ?? ProfileStatus::Active;
            $profile->save();

            $this->syncEducations($user->getKey(), $validated['educations'] ?? []);
        });

        return redirect()->back()->with('status', 'Tutor updated successfully.');
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
            ->where('status', TaxonomyStatus::Active)
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
            ->where('status', TaxonomyStatus::Active)
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
            ->where('status', TaxonomyStatus::Active)
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
            ->where('status', TaxonomyStatus::Active)
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
            ->where('status', TaxonomyStatus::Active)
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'name' => $city->name,
                'city_id' => null,
            ]);

        $areaLocations = Area::query()
            ->where('status', TaxonomyStatus::Active)
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

    /**
     * Update tutor status between active and suspended.
     */
    public function updateStatus(UserStatusUpdateRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Tutor) {
            abort(404);
        }

        $user->forceFill([
            'status' => $request->string('status')->toString(),
        ])->save();

        return redirect()->back()->with('status', 'Tutor status updated successfully.');
    }

    /**
     * Move tutor to recycle bin.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Tutor) {
            abort(404);
        }

        $user->delete();

        return redirect()->back()->with('status', 'Tutor moved to recycle bin.');
    }

    /**
     * Restore tutor from recycle bin.
     */
    public function restore(User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Tutor) {
            abort(404);
        }

        if (! $user->trashed()) {
            return redirect()->route('admin.tutors.index', ['trash' => 1])->with('status', 'Tutor is already active.');
        }

        $user->restore();

        return redirect()->route('admin.tutors.index', ['trash' => 1])->with('status', 'Tutor restored successfully.');
    }

    /**
     * Restore all soft deleted tutors from recycle bin.
     */
    public function restoreAll(): RedirectResponse
    {
        $count = User::query()
            ->onlyTrashed()
            ->where('role', UserRole::Tutor)
            ->restore();

        return redirect()->back()->with('status', "Restored {$count} tutor(s) from recycle bin.");
    }

    /**
     * Permanently delete a tutor from recycle bin.
     */
    public function forceDelete(User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Tutor) {
            abort(404);
        }

        if (! $user->trashed()) {
            return redirect()->back()->withErrors([
                'user' => 'Only trashed tutors can be permanently deleted.',
            ]);
        }

        $user->forceDelete();

        return redirect()->back()->with('status', 'Tutor permanently deleted.');
    }

    /**
     * Empty tutor recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = User::query()
            ->onlyTrashed()
            ->where('role', UserRole::Tutor)
            ->forceDelete();

        return redirect()->back()->with('status', "Deleted {$count} tutor(s) from recycle bin.");
    }

    /**
     * Reset tutor password.
     */
    public function resetPassword(ManagedUserPasswordResetRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Tutor) {
            abort(404);
        }

        $validated = $request->validated();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        Log::info('Admin reset tutor password.', [
            'admin_user_id' => $request->user()?->getKey(),
            'target_user_id' => $user->getKey(),
            'target_role' => 'tutor',
        ]);

        return redirect()->back()->with('status', 'Tutor password reset successfully.');
    }
}
