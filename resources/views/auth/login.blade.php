@extends('layouts.frontend')

@section('title', 'User Login - ' . $settings->site_name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                <!-- Header -->
                <div style="background: linear-gradient(135deg, #16456e 0%, #165752 100%); padding: 30px; color: white; text-align: center;">
                    @if(!empty($settings->logo))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->logo) }}" alt="{{ $settings->site_name }}" style="width:72px;height:72px;object-fit:cover;border-radius:16px;background:rgba(255,255,255,0.16);padding:6px;margin-bottom:14px;">
                    @endif
                    <h2 class="mb-0">Login</h2>
                    <p class="mb-0 mt-2" style="opacity: 0.9;">Welcome Back</p>
                </div>

                <!-- Form -->
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Login Failed!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.submit') }}" novalidate>
                        @csrf

                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" required autofocus>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Your password" required>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn w-100 text-white fw-semibold py-2" style="background: linear-gradient(135deg, #16456e 0%, #165752 100%); border: none; border-radius: 6px; transition: all 0.3s ease;">
                            Login
                        </button>
                    </form>

                    <!-- Register Link -->
                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account? <a href="{{ route('register') }}" class="fw-semibold" style="color: #16456e; text-decoration: none;">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(22, 69, 110, 0.3);
    }
</style>
@endsection
