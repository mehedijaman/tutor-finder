<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleStoreRequest;
use App\Http\Requests\Admin\RoleUpdateRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display role and permission management data.
     */
    public function index(Request $request): Response
    {
        $showTrash = $request->boolean('trash');
        $search = trim($request->string('search')->toString());

        $sort = $request->string('sort')->toString();
        $direction = strtolower($request->string('direction')->toString()) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, ['name', 'created_at'], true)) {
            $sort = 'name';
        }

        $items = Role::query()
            ->with('permissions:name')
            ->when($showTrash, fn ($query) => $query->onlyTrashed())
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
                'created_at' => $role->created_at?->toDateTimeString(),
                'deleted_at' => $role->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/roles/Index', [
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
     * Show the role create screen.
     */
    public function create(): Response
    {
        return inertia('admin/roles/Create', [
            'permissions' => Permission::query()->orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Create a role and optionally attach permissions.
     */
    public function store(RoleStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role = Role::query()->create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('status', 'Role created successfully.');
    }

    /**
     * Show the role edit screen.
     */
    public function edit(Role $role): Response
    {
        return inertia('admin/roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
            ],
            'permissions' => Permission::query()->orderBy('name')->pluck('name'),
        ]);
    }

    /**
     * Update role and its permissions.
     */
    public function update(RoleUpdateRequest $request, Role $role): RedirectResponse
    {
        $validated = $request->validated();

        $role->forceFill([
            'name' => (string) $validated['name'],
        ])->save();

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('status', 'Role updated successfully.');
    }

    /**
     * Move role to recycle bin.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, ['super-admin', 'admin'], true)) {
            return redirect()->back()->withErrors([
                'role' => 'Default admin roles cannot be deleted.',
            ]);
        }

        $role->delete();

        return redirect()->back()->with('status', 'Role moved to recycle bin.');
    }

    /**
     * Restore role from recycle bin.
     */
    public function restore(Role $role): RedirectResponse
    {
        if (! $role->trashed()) {
            return redirect()->route('admin.roles.index', ['trash' => 1])->with('status', 'Role is already active.');
        }

        $role->restore();

        return redirect()->route('admin.roles.index', ['trash' => 1])->with('status', 'Role restored successfully.');
    }

    /**
     * Restore all soft deleted roles from recycle bin.
     */
    public function restoreAll(): RedirectResponse
    {
        $count = Role::query()
            ->onlyTrashed()
            ->whereNotIn('name', ['super-admin', 'admin'])
            ->restore();

        return redirect()->back()->with('status', "Restored {$count} role(s) from recycle bin.");
    }

    /**
     * Permanently delete a role from recycle bin.
     */
    public function forceDelete(Role $role): RedirectResponse
    {
        if (in_array($role->name, ['super-admin', 'admin'], true)) {
            return redirect()->back()->withErrors([
                'role' => 'Default admin roles cannot be permanently deleted.',
            ]);
        }

        if (! $role->trashed()) {
            return redirect()->back()->withErrors([
                'role' => 'Only trashed roles can be permanently deleted.',
            ]);
        }

        $role->forceDelete();

        return redirect()->back()->with('status', 'Role permanently deleted.');
    }

    /**
     * Empty role recycle bin.
     */
    public function emptyRecycleBin(): RedirectResponse
    {
        $count = Role::query()
            ->onlyTrashed()
            ->whereNotIn('name', ['super-admin', 'admin'])
            ->forceDelete();

        return redirect()->back()->with('status', "Deleted {$count} role(s) from recycle bin.");
    }
}
