<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManagedUserUpdateRequest;
use App\Http\Requests\Admin\UserStatusUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class GuardianManagementController extends Controller
{
    /**
     * Display guardians with filters.
     */
    public function index(Request $request): Response
    {
        $filters = [
            ...$request->only(['name', 'phone', 'status']),
            'trash' => $request->boolean('trash'),
        ];

        $guardians = User::query()
            ->where('role', 'guardian')
            ->when($filters['trash'], fn ($query) => $query->onlyTrashed())
            ->when($request->filled('name'), fn ($query) => $query->where('name', 'like', '%'.$request->string('name')->toString().'%'))
            ->when($request->filled('phone'), fn ($query) => $query->where('phone', 'like', '%'.$request->string('phone')->toString().'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return inertia('admin/guardians/Index', [
            'guardians' => $guardians,
            'filters' => $filters,
        ]);
    }

    /**
     * Display a guardian profile.
     */
    public function show(User $user): Response
    {
        if ($user->role !== 'guardian') {
            abort(404);
        }

        return inertia('admin/guardians/Show', [
            'guardian' => $user,
        ]);
    }

    /**
     * Show the guardian edit screen.
     */
    public function edit(User $user): Response
    {
        if ($user->role !== 'guardian') {
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
        if ($user->role !== 'guardian') {
            abort(404);
        }

        $validated = $request->validated();

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ])->save();

        return redirect()->route('admin.guardians.index')->with('status', 'Guardian updated successfully.');
    }

    /**
     * Update guardian status between active and suspended.
     */
    public function updateStatus(UserStatusUpdateRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== 'guardian') {
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
        if ($user->role !== 'guardian') {
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
        if ($user->role !== 'guardian') {
            abort(404);
        }

        $user->restore();

        return redirect()->route('admin.guardians.index', ['trash' => 1])->with('status', 'Guardian restored successfully.');
    }
}
