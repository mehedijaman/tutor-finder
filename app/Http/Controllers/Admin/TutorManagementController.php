<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManagedUserPasswordResetRequest;
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
            'trash' => $request->boolean('trash'),
            'sort' => $sort,
            'direction' => $direction,
        ];

        $items = User::query()
            ->where('role', 'tutor')
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
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'created_at' => $user->created_at?->toDateTimeString(),
                'deleted_at' => $user->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/tutors/Index', [
            'items' => $items,
            'filters' => $filters,
        ]);
    }

    /**
     * Display a tutor profile.
     */
    public function show(User $user): Response
    {
        if ($user->role !== 'tutor') {
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
        if ($user->role !== 'tutor') {
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
        if ($user->role !== 'tutor') {
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
        if ($user->role !== 'tutor') {
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
        if ($user->role !== 'tutor') {
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
        if ($user->role !== 'tutor') {
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
            ->where('role', 'tutor')
            ->restore();

        return redirect()->back()->with('status', "Restored {$count} tutor(s) from recycle bin.");
    }

    /**
     * Permanently delete a tutor from recycle bin.
     */
    public function forceDelete(User $user): RedirectResponse
    {
        if ($user->role !== 'tutor') {
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
            ->where('role', 'tutor')
            ->forceDelete();

        return redirect()->back()->with('status', "Deleted {$count} tutor(s) from recycle bin.");
    }

    /**
     * Reset tutor password.
     */
    public function resetPassword(ManagedUserPasswordResetRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== 'tutor') {
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
