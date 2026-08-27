@extends('layouts.admin')

@section('title', 'Profile')

@section('content')
    <div class="card" style="max-width:640px;">
        <h3 style="margin-top:0;">My Profile</h3>
        <form method="POST" action="{{ route('admin.profile.update') }}">
            @csrf
            @method('PUT')
            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>

            <label for="password">New password</label>
            <input id="password" type="password" name="password">

            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation">

            <button type="submit">Save profile</button>
        </form>
    </div>
@endsection
