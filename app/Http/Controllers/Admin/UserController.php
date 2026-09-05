<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserGroup;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

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
        $data = $this->validatedUser($request);
        $data['avatar'] = $this->storeAvatar($request);

        $user = User::create($data);
        $user->applyRoleIds([$data['role_id']]);
        $user->ensureEmployeeCode();

        Audit::log('user_created', 'Created user '.$user->email, $user, [], 'users');

        return redirect()->route('admin.users.edit', $user)->with('success', 'User created.');
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
        $data = $this->validatedUser($request, $user);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($request->boolean('remove_avatar') && $user->avatarStoragePath()) {
            try {
                Storage::disk('public')->delete($user->avatarStoragePath());
            } catch (\Throwable) {
            }
            $data['avatar'] = null;
        } elseif ($request->hasFile('avatar')) {
            if ($user->avatarStoragePath()) {
                try {
                    Storage::disk('public')->delete($user->avatarStoragePath());
                } catch (\Throwable) {
                }
            }
            $data['avatar'] = $this->storeAvatar($request);
        } else {
            unset($data['avatar']);
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
        $user->ensureEmployeeCode();

        Audit::log('user_updated', 'Updated user '.$user->email, $user, [], 'users');

        return redirect()->route('admin.users.edit', $user)->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'You cannot delete your own account.');

        if ($user->avatarStoragePath()) {
            try {
                Storage::disk('public')->delete($user->avatarStoragePath());
            } catch (\Throwable) {
            }
        }

        $email = $user->email;
        $user->delete();

        Audit::log('user_deleted', 'Deleted user '.$email, null, ['email' => $email], 'users');

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->applyRoleIds([$data['role_id']]);

        Audit::log('user_role_updated', 'Updated role for '.$user->email, $user, [
            'role_id' => $data['role_id'],
        ], 'users');

        return redirect()->route('admin.users.index')->with('success', 'User role updated.');
    }

    public function assignRoles(User $user)
    {
        return view('admin.user.assign-roles', [
            'user' => $user->load(['roles', 'role', 'group']),
            'roles' => Role::query()->withCount('permissions')->orderBy('name')->get(),
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

        Audit::log('user_roles_updated', 'Updated roles for '.$user->email, $user, [
            'roles' => $data['roles'] ?? [],
        ], 'users');

        return redirect()->route('admin.users.index')->with('success', 'User roles updated.');
    }

    public function resetPassword(User $user)
    {
        $user->update(['password' => 'password123']);

        Audit::log('user_password_reset', 'Reset password for '.$user->email, $user, [], 'users');

        return back()->with('success', 'Password reset to password123.');
    }

    public function printCard(User $user): View
    {
        $user->loadMissing(['role', 'roles', 'group']);
        $user->ensureEmployeeCode();

        return view('admin.user.card', [
            'user' => $user,
            'settings' => Setting::get_settings(),
        ]);
    }

    private function validatedUser(Request $request, ?User $user = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email'.($user ? ','.$user->id : ''),
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'role_id' => 'required|exists:roles,id',
            'user_group_id' => 'nullable|exists:user_groups,id',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_avatar' => 'nullable|boolean',
            'job_title' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:80',
            'employee_code' => 'nullable|string|max:40|unique:users,employee_code'.($user ? ','.$user->id : ''),
        ];

        foreach (['avatar', 'job_title', 'phone', 'employee_code'] as $col) {
            if (! Schema::hasColumn('users', $col)) {
                unset($rules[$col]);
            }
        }

        return $request->validate($rules);
    }

    private function storeAvatar(Request $request): ?string
    {
        if (! $request->hasFile('avatar') || ! Schema::hasColumn('users', 'avatar')) {
            return null;
        }

        return $request->file('avatar')->store('avatars', 'public');
    }
}
