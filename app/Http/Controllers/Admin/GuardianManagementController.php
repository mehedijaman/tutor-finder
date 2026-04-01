<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManagedUserPasswordResetRequest;
use App\Http\Requests\Admin\ManagedUserStoreRequest;
use App\Http\Requests\Admin\ManagedUserUpdateRequest;
use App\Http\Requests\Admin\UserStatusUpdateRequest;
use App\Models\GuardianProfile;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Response;

class GuardianManagementController extends Controller
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
     * Display guardians with filters.
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
            ->where('role', UserRole::Guardian)
            ->with(['guardianProfile', 'latestVerificationRequest.invoice'])
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
                    'occupation' => $user->guardianProfile?->occupation,
                    'address' => $user->guardianProfile?->address,
                    'created_at' => $user->created_at?->toDateTimeString(),
                    'deleted_at' => $user->deleted_at?->toDateTimeString(),
                ];
            });

        return inertia('admin/guardians/Index', [
            'items' => $items,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the create guardian screen.
     */
    public function create(): Response
    {
        return inertia('admin/guardians/Create');
    }

    /**
     * Store a new guardian account.
     */
    public function store(ManagedUserStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $user = User::forceCreate([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'role' => UserRole::Guardian,
                'status' => $validated['status'],
                'email_verified_at' => now(),
            ]);

            GuardianProfile::forceCreate([
                'user_id' => $user->id,
                'guardian_name' => $validated['guardian_name'] ?? $validated['name'],
                'occupation' => $validated['occupation'] ?? null,
                'address' => $validated['address'] ?? null,
                'phone_alt' => $validated['phone_alt'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('admin.guardians.index')->with('status', 'Guardian created successfully.');
    }

    /**
     * Display a guardian profile.
     */
    public function show(User $user): Response
    {
        if ($user->role !== UserRole::Guardian) {
            abort(404);
        }

        $user->load(['guardianProfile']);

        $verificationRequest = VerificationRequest::query()
            ->with('invoice')
            ->where('user_id', $user->getKey())
            ->latest('id')
            ->first();

        return inertia('admin/guardians/Show', [
            'guardian' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'verification_status' => $user->verification_status,
                'created_at' => $user->created_at?->toDateTimeString(),
            ],
            'profile' => [
                'occupation' => $user->guardianProfile?->occupation,
                'address' => $user->guardianProfile?->address,
                'phone_alt' => $user->guardianProfile?->phone_alt,
                'notes' => $user->guardianProfile?->notes,
            ],
            'verification' => $verificationRequest ? [
                'id' => $verificationRequest->id,
                'status' => $verificationRequest->status,
                'role' => $verificationRequest->role,
                'submitted_at' => $verificationRequest->submitted_at?->toDateTimeString(),
                'reviewed_at' => $verificationRequest->reviewed_at?->toDateTimeString(),
                'invoice' => $verificationRequest->invoice ? [
                    'id' => $verificationRequest->invoice->id,
                    'status' => $verificationRequest->invoice->status,
                    'amount' => $verificationRequest->invoice->amount,
                ] : null,
            ] : null,
        ]);
    }

    /**
     * Show the guardian edit screen.
     */
    public function edit(User $user): Response
    {
        if ($user->role !== UserRole::Guardian) {
            abort(404);
        }

        return inertia('admin/guardians/Edit', [
            'guardian' => $user,
        ]);
    }

    /**
     * Update a guardian profile.
     */
    public function update(ManagedUserUpdateRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Guardian) {
            abort(404);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $user) {
            $user->forceFill([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ])->save();

            GuardianProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'occupation' => $validated['occupation'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'phone_alt' => $validated['phone_alt'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]
            );
        });

        return back()->with('status', 'Guardian updated successfully.');
    }

    /**
     * Update guardian status between active and suspended.
     */
    public function updateStatus(UserStatusUpdateRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Guardian) {
            abort(404);
        }

        $user->forceFill([
            'status' => $request->string('status')->toString(),
        ])->save();

        return redirect()->back()->with('status', 'Guardian status updated successfully.');
    }

    /**
     * Move guardian to recycle bin.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Guardian) {
            abort(404);
        }

        $user->delete();

        return redirect()->back()->with('status', 'Guardian moved to recycle bin.');
    }

    /**
     * Restore guardian from recycle bin.
     */
    public function restore(User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Guardian) {
            abort(404);
        }

        if (! $user->trashed()) {
            return redirect()->route('admin.guardians.index', ['trash' => 1])->with('status', 'Guardian is already active.');
        }

        $user->restore();

        return redirect()->route('admin.guardians.index', ['trash' => 1])->with('status', 'Guardian restored successfully.');
    }

    /**
     * Restore all soft deleted guardians from recycle bin.
     */
    public function restoreAll(): RedirectResponse
    {
        $count = User::query()
            ->onlyTrashed()
            ->where('role', UserRole::Guardian)
            ->restore();

        return redirect()->back()->with('status', "Restored {$count} guardian(s) from recycle bin.");
    }

    /**
     * Permanently delete a guardian from recycle bin.
     */
    public function forceDelete(User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Guardian) {
            abort(404);
        }

        if (! $user->trashed()) {
            return redirect()->back()->withErrors([
                'user' => 'Only trashed guardians can be permanently deleted.',
            ]);
        }

        $user->forceDelete();

        return redirect()->back()->with('status', 'Guardian permanently deleted.');
    }

    /**
     * Empty guardian recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = User::query()
            ->onlyTrashed()
            ->where('role', UserRole::Guardian)
            ->forceDelete();

        return redirect()->back()->with('status', "Deleted {$count} guardian(s) from recycle bin.");
    }

    /**
     * Reset guardian password.
     */
    public function resetPassword(ManagedUserPasswordResetRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Guardian) {
            abort(404);
        }

        $validated = $request->validated();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        Log::info('Admin reset guardian password.', [
            'admin_user_id' => $request->user()?->getKey(),
            'target_user_id' => $user->getKey(),
            'target_role' => 'guardian',
        ]);

        return redirect()->back()->with('status', 'Guardian password reset successfully.');
    }
}
