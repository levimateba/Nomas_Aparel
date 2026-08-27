@extends('layouts.auth')

@section('title', 'Register - ' . ($settings->site_name ?? 'Store'))
@section('heading', 'Create account')
@section('subheading', 'Join ' . ($settings->site_name ?? 'the store'))

@section('content')
    <form method="POST" action="{{ route('register.submit') }}">
        @csrf
        <label for="name">Full name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Your full name" required>

        <label for="email">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" required>

        <label for="password">Password</label>
        <input id="password" type="password" name="password" placeholder="At least 8 characters" required>

        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirm your password" required>

        <button class="btn" type="submit">Create account</button>
    </form>
    <div class="auth-links">
        Already have an account? <a href="{{ route('login') }}">Login here</a>
    </div>
@endsection
