<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManagedUserPasswordResetRequest;
use App\Http\Requests\Admin\ManagedUserStoreRequest;
use App\Http\Requests\Admin\ManagedUserUpdateRequest;
use App\Http\Requests\Admin\UserStatusUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        return inertia('admin/tutors/Show', [
            'tutor' => $user,
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
    public function update(ManagedUserUpdateRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Tutor) {
            abort(404);
        }

        $validated = $request->validated();

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ])->save();

        return redirect()->route('admin.tutors.index')->with('status', 'Tutor updated successfully.');
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
