@extends('layouts.auth')

@section('title', 'Student Registration — SkillForge')

@section('content')

<div class="auth-page auth-page-full">
    <div class="auth-shell">
        <div class="auth-panel auth-panel-brand">
            <div class="auth-panel-content">
                <a href="{{ route('home') }}" class="font-display text-2xl font-bold text-white">SkillForge</a>
                <h1 class="mt-10 font-display text-3xl sm:text-4xl font-bold text-white leading-tight">Start learning today</h1>
                <p class="mt-4 text-white/80 leading-relaxed max-w-sm">
                    Create your student account to explore courses, enrol in classes, and access assessments with AI-powered feedback.
                </p>
                <div class="mt-10 flex items-center gap-6">
                    <div>
                        <p class="font-display text-2xl font-bold text-white">12k+</p>
                        <p class="mt-1 text-xs uppercase tracking-wider text-white/70">Learners</p>
                    </div>
                    <div>
                        <p class="font-display text-2xl font-bold text-white">120+</p>
                        <p class="mt-1 text-xs uppercase tracking-wider text-white/70">Courses</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-panel auth-panel-form">
            <div class="auth-form-container">
                <a href="{{ route('register') }}" class="auth-back-link">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to account type
                </a>

                <div class="auth-form-wrap">
                    <span class="auth-role-badge">Student</span>
                    <h2 class="mt-4 font-display text-2xl font-bold text-gray-900">Student registration</h2>
                    <p class="mt-2 text-sm text-gray-500">Provide your details to create a student account.</p>

                    @include('partials.auth-alerts')

                    <form action="{{ route('register.student.submit') }}" method="post" class="mt-8 space-y-5" data-validate="register-student" novalidate>
                        @csrf
                        <div class="form-field">
                            <label for="name" class="auth-label">Full name</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" class="auth-input @error('name') is-invalid @enderror">
                            @include('partials.field-error', ['name' => 'name'])
                        </div>
                        <div class="form-field">
                            <label for="email" class="auth-label">Email address</label>
                            <input id="email" type="text" name="email" value="{{ old('email') }}" placeholder="you@example.com" class="auth-input @error('email') is-invalid @enderror">
                            @include('partials.field-error', ['name' => 'email'])
                        </div>
                        <div class="form-field">
                            <label for="password" class="auth-label">Password</label>
                            <input id="password" type="password" name="password" placeholder="Create a password" class="auth-input @error('password') is-invalid @enderror">
                            @include('partials.field-error', ['name' => 'password'])
                        </div>
                        <div class="form-field">
                            <label for="password_confirmation" class="auth-label">Confirm password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Repeat your password" class="auth-input @error('password_confirmation') is-invalid @enderror">
                            @include('partials.field-error', ['name' => 'password_confirmation'])
                        </div>
                        <div class="form-field">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="terms" value="1" class="checkout-checkbox mt-0.5 @error('terms') is-invalid @enderror" @checked(old('terms'))>
                            <span class="text-sm text-gray-600 leading-relaxed">
                                I agree to the
                                <a href="#" class="font-medium text-brand-dark hover:text-brand">Terms of Service</a>
                                and
                                <a href="#" class="font-medium text-brand-dark hover:text-brand">Privacy Policy</a>
                            </span>
                        </label>
                        @include('partials.field-error', ['name' => 'terms'])
                        </div>
                        <button type="submit" class="auth-submit">Create student account</button>
                    </form>

                    <p class="mt-8 text-center text-sm text-gray-500">
                        Want to teach instead?
                        <a href="{{ route('register.instructor') }}" class="font-semibold text-brand-dark hover:text-brand">Register as instructor</a>
                    </p>
                    <p class="mt-3 text-center text-sm text-gray-500">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-semibold text-brand-dark hover:text-brand">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
