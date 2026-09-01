@extends('layouts.auth')

@section('title', 'Login — SkillForge')

@section('content')

<div class="auth-page auth-page-full">
    <div class="auth-shell">
        {{-- Left panel --}}
        <div class="auth-panel auth-panel-brand">
            <div class="auth-panel-content">
                <a href="{{ route('home') }}" class="font-display text-2xl font-bold text-white">SkillForge</a>
                <h1 class="mt-10 font-display text-3xl sm:text-4xl font-bold text-white leading-tight">Welcome back</h1>
                <p class="mt-4 text-white/80 leading-relaxed max-w-sm">
                    Sign in to continue your learning journey, track progress, and access your enrolled courses.
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

        {{-- Right panel --}}
        <div class="auth-panel auth-panel-form">
            <div class="auth-form-container">
                <a href="{{ route('home') }}" class="auth-back-link lg:hidden">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to home
                </a>

                <div class="auth-form-wrap">
                <a href="{{ route('home') }}" class="font-display text-xl font-bold text-gray-900 lg:hidden">SkillForge</a>
                <h2 class="mt-6 lg:mt-0 font-display text-2xl font-bold text-gray-900">Sign in</h2>
                <p class="mt-2 text-sm text-gray-500">Enter your credentials to access your account.</p>

                <form action="{{ route('dashboard') }}" method="get" class="mt-8 space-y-5">
                    <div>
                        <label for="email" class="auth-label">Email address</label>
                        <input id="email" type="email" name="email" placeholder="you@example.com" class="auth-input" required>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="auth-label !mb-0">Password</label>
                            <a href="#" class="text-xs font-medium text-brand-dark hover:text-brand">Forgot password?</a>
                        </div>
                        <input id="password" type="password" name="password" placeholder="Enter your password" class="auth-input" required>
                    </div>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" class="checkout-checkbox">
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>
                    <button type="submit" class="auth-submit">Sign in</button>
                </form>

                <div class="auth-divider">
                    <span>or continue with</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" class="auth-social-btn">
                        <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Google
                    </button>
                    <button type="button" class="auth-social-btn">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        Facebook
                    </button>
                </div>

                <p class="mt-8 text-center text-sm text-gray-500">
                    Don't have an account?
                    <a href="#" class="font-semibold text-brand-dark hover:text-brand">Create account</a>
                </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
