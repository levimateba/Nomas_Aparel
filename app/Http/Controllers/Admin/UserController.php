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
        $users = User::with(['role', 'group'])->latest()->paginate(12);
        $roles = Role::orderBy('name')->get();

        return view('admin.user.index', compact('users', 'roles'));
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

        $role = Role::findOrFail($data['role_id']);
        $data['is_admin'] = $role->slug === 'admin';

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.user.edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
            'groups' => UserGroup::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role_id' => 'required|exists:roles,id',
            'user_group_id' => 'nullable|exists:user_groups,id',
        ]);

        $role = Role::findOrFail($data['role_id']);
        $data['is_admin'] = $role->slug === 'admin';

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($data['role_id']);

        $user->update([
            'role_id' => $role->id,
            'is_admin' => $role->slug === 'admin',
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User role updated.');
    }
}
