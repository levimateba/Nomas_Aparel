<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Support\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        $catalogNames = PermissionCatalog::names();
        $permissions = Permission::query()->orderBy('name')->get();
        $byName = $permissions->keyBy('name');

        $grouped = [];
        foreach (PermissionCatalog::grouped() as $group => $items) {
            foreach ($items as $name => $meta) {
                $grouped[$group][] = [
                    'name' => $name,
                    'meta' => $meta,
                    'model' => $byName->get($name),
                    'in_db' => $byName->has($name),
                ];
            }
        }

        $orphans = $permissions
            ->reject(fn (Permission $p) => in_array($p->name, $catalogNames, true))
            ->values();

        return view('admin.permission.index', [
            'grouped' => $grouped,
            'orphans' => $orphans,
            'stats' => [
                'catalog' => count($catalogNames),
                'groups' => count($grouped),
                'in_db' => $permissions->count(),
                'orphans' => $orphans->count(),
                'missing' => collect($catalogNames)->reject(fn ($n) => $byName->has($n))->count(),
            ],
        ]);
    }

    public function sync(): RedirectResponse
    {
        $created = 0;
        foreach (PermissionCatalog::catalog() as $name => $meta) {
            $row = Permission::query()->updateOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name, '_'),
                    'description' => $meta['description'] ?? null,
                ]
            );
            if ($row->wasRecentlyCreated) {
                $created++;
            }
        }

        return back()->with('success', $created > 0
            ? "Catalogue synced. {$created} permission(s) added."
            : 'Catalogue already in sync.');
    }

    public function prune(): RedirectResponse
    {
        $catalogNames = PermissionCatalog::names();
        $obsolete = [
            'create_permissions',
            'edit_permissions',
            'delete_permissions',
            'Manage Content',
            'Manage Users',
            'Manage Settings',
        ];

        $removed = 0;
        DB::transaction(function () use ($catalogNames, $obsolete, &$removed) {
            $query = Permission::query()->where(function ($q) use ($catalogNames, $obsolete) {
                $q->whereNotIn('name', $catalogNames)
                    ->orWhereIn('name', $obsolete);
            });

            $ids = $query->pluck('id');
            if ($ids->isEmpty()) {
                return;
            }

            DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
            $removed = Permission::query()->whereIn('id', $ids)->delete();
        });

        return back()->with('success', $removed > 0
            ? "Removed {$removed} unused permission(s)."
            : 'No unused permissions to remove.');
    }

    public function create(): View
    {
        return view('admin.permission.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name|regex:/^[a-z][a-z0-9_]*$/',
            'description' => 'nullable|string|max:1000',
        ]);

        Permission::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name'], '_'),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created. Prefer adding it to config/permissions.php so it stays in the catalogue.');
    }

    public function edit(Permission $permission): View
    {
        return view('admin.permission.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
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

    public function destroy(Permission $permission): RedirectResponse
    {
        DB::table('permission_role')->where('permission_id', $permission->id)->delete();
        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted.');
    }
}
