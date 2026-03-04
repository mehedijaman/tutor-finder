<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserStoreRequest;
use App\Http\Requests\Admin\AdminUserUpdateRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class AdminUserController extends Controller
{
    /**
     * Display all admin users.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $search = trim($request->string('search')->toString());

        $sort = $request->string('sort')->toString();
        $direction = strtolower($request->string('direction')->toString()) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, ['name', 'email', 'status', 'created_at'], true)) {
            $sort = 'name';
        }

        $items = User::query()
            ->where('role', 'admin')
            ->when($showTrash, fn ($query) => $query->onlyTrashed())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->with(['roles:name', 'permissions:name'])
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'roles' => $user->roles->pluck('name')->values()->all(),
                'permissions' => $user->permissions->pluck('name')->values()->all(),
                'created_at' => $user->created_at?->toDateTimeString(),
                'deleted_at' => $user->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/users/Index', [
            'items' => $items,
            'filters' => [
                'trash' => $showTrash,
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    /**
     * Show the create-admin form.
     */
    public function create(): Response
    {
        return inertia('admin/users/Create', [
            'roles' => Role::query()->orderBy('name')->pluck('name'),
            'permissions' => Permission::query()->orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Show the edit-admin form.
     */
    public function edit(User $user): Response
    {
        if ($user->role !== UserRole::Admin) {
            abort(404);
        }

        return inertia('admin/users/Edit', [
            'adminUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'roles' => $user->roles->pluck('name')->values()->all(),
                'permissions' => $user->permissions->pluck('name')->values()->all(),
            ],
            'roles' => Role::query()->orderBy('name')->pluck('name'),
            'permissions' => Permission::query()->orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Store a newly created admin user.
     */
    public function store(AdminUserStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => strtolower((string) $validated['email']),
            'phone' => null,
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $user->syncRoles($validated['roles'] ?? []);
        $user->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.users.index')->with('status', 'Admin user created successfully.');
    }

    /**
     * Update admin roles and permissions.
     */
    public function update(AdminUserUpdateRequest $request, User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Admin) {
            abort(404);
        }

        $validated = $request->validated();

        $user->forceFill([
            'name' => $validated['name'],
            'email' => strtolower((string) $validated['email']),
            'status' => (string) $validated['status'],
        ])->save();

        $user->syncRoles($validated['roles'] ?? []);
        $user->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.users.index')->with('status', 'Admin access updated successfully.');
    }

    /**
     * Move an admin user to recycle bin.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Admin) {
            abort(404);
        }

        if ($request->user()?->is($user)) {
            return redirect()->back()->withErrors([
                'user' => 'You cannot delete your own admin account.',
            ]);
        }

        $user->delete();

        return redirect()->back()->with('status', 'Admin user moved to recycle bin.');
    }

    /**
     * Restore a soft deleted admin user from recycle bin.
     */
    public function restore(User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Admin) {
            abort(404);
        }

        if (! $user->trashed()) {
            return redirect()->route('admin.users.index', ['trash' => 1])->with('status', 'Admin user is already active.');
        }

        $user->restore();

        return redirect()->route('admin.users.index', ['trash' => 1])->with('status', 'Admin user restored successfully.');
    }

    /**
     * Restore all soft deleted admin users from recycle bin.
     */
    public function restoreAll(Request $request): RedirectResponse
    {
        $count = User::query()
            ->onlyTrashed()
            ->where('role', 'admin')
            ->when($request->user(), fn ($query) => $query->whereKeyNot($request->user()->getKey()))
            ->restore();

        return redirect()->back()->with('status', "Restored {$count} admin user(s) from recycle bin.");
    }

    /**
     * Permanently delete an admin user from recycle bin.
     */
    public function forceDelete(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Admin) {
            abort(404);
        }

        if ($request->user()?->is($user)) {
            return redirect()->back()->withErrors([
                'user' => 'You cannot permanently delete your own admin account.',
            ]);
        }

        if (! $user->trashed()) {
            return redirect()->back()->withErrors([
                'user' => 'Only trashed admin users can be permanently deleted.',
            ]);
        }

        $user->forceDelete();

        return redirect()->back()->with('status', 'Admin user permanently deleted.');
    }

    /**
     * Empty admin users recycle bin.
     */
    public function emptyRecycleBin(Request $request): RedirectResponse
    {
        $count = User::query()
            ->onlyTrashed()
            ->where('role', 'admin')
            ->when($request->user(), fn ($query) => $query->whereKeyNot($request->user()->getKey()))
            ->forceDelete();

        return redirect()->back()->with('status', "Deleted {$count} admin user(s) from recycle bin.");
    }
}
