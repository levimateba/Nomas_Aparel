@extends('layouts.frontend')

@section('title', 'Create Account - ' . $settings->site_name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                <!-- Header -->
                <div style="background: linear-gradient(135deg, #16456e 0%, #165752 100%); padding: 30px; color: white; text-align: center;">
                    @if(!empty($settings->logo))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->logo) }}" alt="{{ $settings->site_name }}" style="width:72px;height:72px;object-fit:cover;border-radius:16px;background:rgba(255,255,255,0.16);padding:6px;margin-bottom:14px;">
                    @endif
                    <h2 class="mb-0">Create Account</h2>
                    <p class="mb-0 mt-2" style="opacity: 0.9;">Join {{ $settings->site_name }} Today</p>
                </div>

                <!-- Form -->
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.submit') }}" novalidate>
                        @csrf

                        <!-- Name Field -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Your Full Name" required>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="your@email.com" required>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="At least 8 characters" required>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm your password" required>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn w-100 text-white fw-semibold py-2" style="background: linear-gradient(135deg, #16456e 0%, #165752 100%); border: none; border-radius: 6px; transition: all 0.3s ease;">
                            Create Account
                        </button>
                    </form>

                    <!-- Login Link -->
                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account? <a href="{{ route('login') }}" class="fw-semibold" style="color: #16456e; text-decoration: none;">Login here</a></p>
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
