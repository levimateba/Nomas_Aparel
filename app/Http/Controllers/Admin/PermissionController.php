<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::query()->orderBy('name')->paginate(12);
        $catalogNames = PermissionCatalog::names();

        return view('admin.permission.index', [
            'permissions' => $permissions,
            'permissionGroups' => PermissionCatalog::grouped(),
            'uncataloguedCount' => Permission::query()->whereNotIn('name', $catalogNames)->count(),
        ]);
    }

    public function create()
    {
        return view('admin.permission.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Permission::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name'], '_'),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created.');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permission.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,'.$permission->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $permission->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name'], '_'),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted.');
    }
}
