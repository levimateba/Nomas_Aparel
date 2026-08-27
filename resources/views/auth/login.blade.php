@extends('layouts.auth')

@section('title', 'Login - ' . ($settings->site_name ?? 'Store'))
@section('heading', 'Login')
@section('subheading', 'Welcome back')

@section('content')
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <label for="email">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" required autofocus>

        <label for="password">Password</label>
        <input id="password" type="password" name="password" placeholder="Your password" required>

        <button class="btn" type="submit">Login</button>
    </form>
    <div class="auth-links">
        Don't have an account? <a href="{{ route('register') }}">Register here</a>
    </div>
@endsection
