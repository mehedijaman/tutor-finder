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

        $roles = Role::query()
            ->with('permissions:name')
            ->when($showTrash, fn ($query) => $query->onlyTrashed())
            ->orderBy('name')
            ->paginate(15)
            ->through(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values(),
                'deleted_at' => $role->deleted_at?->toDateTimeString(),
            ]);

        return inertia('admin/roles/Index', [
            'roles' => $roles,
            'permissions' => Permission::query()->orderBy('name')->pluck('name'),
            'filters' => [
                'trash' => $showTrash,
            ],
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
        $role->restore();

        return redirect()->route('admin.roles.index', ['trash' => 1])->with('status', 'Role restored successfully.');
    }
}
