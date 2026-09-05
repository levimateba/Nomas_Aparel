<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Audit;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->withCount('permissions')->orderBy('name')->paginate(12);

        return view('admin.role.index', [
            'roles' => $roles,
            'stats' => [
                'total' => Role::count(),
                'with_permissions' => Role::has('permissions')->count(),
                'permission_links' => (int) DB::table('permission_role')->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.role.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'description' => $data['description'] ?? null,
        ]);

        Audit::log('role_created', 'Created role '.$role->name, $role, [], 'access');

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        return view('admin.role.edit', [
            'role' => $role->loadCount('permissions'),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $slug = $role->slug;
        if (strcasecmp((string) $role->name, (string) $data['name']) !== 0) {
            $slug = $this->uniqueSlug($data['name'], $role->id);
        }

        $role->update([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
        ]);

        Audit::log('role_updated', 'Updated role '.$role->name, $role, [], 'access');

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('success', 'Role updated. You can assign permissions next.');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->slug, ['super-admin', 'admin'], true)) {
            throw ValidationException::withMessages([
                'role' => 'The '.$role->name.' role cannot be deleted.',
            ]);
        }

        if ($role->assignedUsers()->exists() || $role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => 'This role is assigned to users. Reassign them first.',
            ]);
        }

        $name = $role->name;
        $role->permissions()->detach();
        $role->delete();

        Audit::log('role_deleted', 'Deleted role '.$name, null, ['name' => $name], 'access');

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }

    public function assignPermissions(Role $role)
    {
        $permissions = Permission::query()->orderBy('name')->get();

        return view('admin.role.assign-permissions', [
            'role' => $role->load('permissions'),
            'permissions' => $permissions,
            'rolePermissions' => $role->permissions->pluck('id')->all(),
            'permissionGroups' => PermissionCatalog::grouped(),
            'permissionsByName' => $permissions->keyBy('name'),
        ]);
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        Audit::log('role_permissions_changed', 'Updated permissions for role '.$role->name, $role, [
            'permission_count' => count($data['permissions'] ?? []),
        ], 'access');

        return redirect()
            ->route('admin.roles.assign-permissions', $role)
            ->with('success', 'Permissions updated for '.$role->name.'.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'role';
        $slug = $base;
        $i = 2;

        while (
            Role::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
