<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The provided credentials do not match our records.']);
        }

        $user = Auth::user();
        $user?->loadMissing(['roles', 'role']);

        if (! $user || ! $user->isAdminUser()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'You do not have access to the admin panel.']);
        }

        $request->session()->regenerate();

        Audit::log('login', 'User logged in', $user, [], 'auth');

        $home = $user->preferredAdminHomeRoute();

        return redirect()->intended(route($home));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            Audit::log('logout', 'User logged out', $user, [], 'auth');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}

