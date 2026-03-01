<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManagedUserUpdateRequest;
use App\Http\Requests\Admin\UserStatusUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class TutorManagementController extends Controller
{
    /**
     * Display tutors with filters.
     */
    public function index(Request $request): Response
    {
        $filters = [
            ...$request->only(['name', 'phone', 'status']),
            'trash' => $request->boolean('trash'),
        ];

        $tutors = User::query()
            ->where('role', 'tutor')
            ->when($filters['trash'], fn ($query) => $query->onlyTrashed())
            ->when($request->filled('name'), fn ($query) => $query->where('name', 'like', '%'.$request->string('name')->toString().'%'))
            ->when($request->filled('phone'), fn ($query) => $query->where('phone', 'like', '%'.$request->string('phone')->toString().'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return inertia('admin/tutors/Index', [
            'tutors' => $tutors,
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

        $user->restore();

        return redirect()->route('admin.tutors.index', ['trash' => 1])->with('status', 'Tutor restored successfully.');
    }
}
