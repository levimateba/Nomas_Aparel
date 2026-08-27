<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'roles', 'group'])->latest()->paginate(12);

        return view('admin.user.index', [
            'users' => $users,
            'stats' => [
                'total' => User::count(),
                'admins' => User::where('is_admin', true)->count(),
                'staff' => User::query()
                    ->where(function ($query) {
                        $query->where('is_admin', true)
                            ->orWhereHas('roles', function ($roles) {
                                $roles->whereIn('slug', ['admin', 'super-admin', 'manager', 'staff', 'cashier', 'stock-manager']);
                            });
                    })
                    ->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.user.create', [
            'roles' => Role::orderBy('name')->get(),
            'groups' => UserGroup::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role_id' => 'required|exists:roles,id',
            'user_group_id' => 'nullable|exists:user_groups,id',
        ]);

        $user = User::create($data);
        $user->applyRoleIds([$data['role_id']]);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.user.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::orderBy('name')->get(),
            'groups' => UserGroup::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role_id' => 'required|exists:roles,id',
            'user_group_id' => 'nullable|exists:user_groups,id',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $previousPrimary = $user->role_id;
        $user->update($data);

        $roleIds = $user->roles()->pluck('roles.id')
            ->reject(fn ($id) => (int) $id === (int) $previousPrimary)
            ->push($data['role_id'])
            ->unique()
            ->values()
            ->all();

        $user->applyRoleIds($roleIds ?: [$data['role_id']]);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'You cannot delete your own account.');

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->applyRoleIds([$data['role_id']]);

        return redirect()->route('admin.users.index')->with('success', 'User role updated.');
    }

    public function assignRoles(User $user)
    {
        return view('admin.user.assign-roles', [
            'user' => $user->load('roles'),
            'roles' => Role::orderBy('name')->get(),
            'userRoles' => $user->roles->pluck('id')->all(),
        ]);
    }

    public function updateRoles(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user->applyRoleIds($data['roles'] ?? []);

        return redirect()->route('admin.users.index')->with('success', 'User roles updated.');
    }

    public function resetPassword(User $user)
    {
        $user->update(['password' => 'password123']);

        return back()->with('success', 'Password reset to password123.');
    }
}
