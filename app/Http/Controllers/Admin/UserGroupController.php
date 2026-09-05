<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserGroup;
use Illuminate\Http\Request;

class UserGroupController extends Controller
{
    public function index()
    {
        $groups = UserGroup::withCount('users')->latest()->paginate(12);

        return view('admin.user-group.index', [
            'groups' => $groups,
            'stats' => [
                'total' => UserGroup::count(),
                'with_users' => UserGroup::has('users')->count(),
                'members' => (int) UserGroup::withCount('users')->get()->sum('users_count'),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.user-group.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:user_groups,name',
            'description' => 'nullable|string|max:1000',
        ]);

        UserGroup::create($data);

        return redirect()->route('admin.user-groups.index')->with('success', 'User group created.');
    }

    public function edit(UserGroup $user_group)
    {
        return view('admin.user-group.edit', ['group' => $user_group]);
    }

    public function update(Request $request, UserGroup $user_group)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:user_groups,name,' . $user_group->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $user_group->update($data);

        return redirect()->route('admin.user-groups.index')->with('success', 'User group updated.');
    }

    public function destroy(UserGroup $user_group)
    {
        $user_group->delete();

        return redirect()->route('admin.user-groups.index')->with('success', 'User group deleted.');
    }
}
