@extends('layouts.auth')

@section('title', 'Login — OCMS')

@section('content')

<div class="auth-page auth-page-full">
    <div class="auth-shell">
        <div class="auth-panel auth-panel-brand">
            <div class="auth-panel-content">
                <a href="{{ route('home') }}" class="font-display text-2xl font-bold text-white">OCMS</a>
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

        <div class="auth-panel auth-panel-form">
            <div class="auth-form-container">
                <a href="{{ route('home') }}" class="auth-back-link lg:hidden">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to home
                </a>

                <div class="auth-form-wrap">
                <a href="{{ route('home') }}" class="font-display text-xl font-bold text-gray-900 lg:hidden">OCMS</a>
                <h2 class="mt-6 lg:mt-0 font-display text-2xl font-bold text-gray-900">Sign in</h2>
                <p class="mt-2 text-sm text-gray-500">Enter your credentials to access your account.</p>

                @include('partials.auth-alerts')

                <form action="{{ route('login.submit') }}" method="post" class="mt-8 space-y-5" data-validate="login" novalidate>
                    @csrf
                    <div class="form-field">
                        <label for="email" class="auth-label">Email address</label>
                        <input id="email" type="text" name="email" value="{{ old('email') }}" placeholder="you@example.com" class="auth-input @error('email') is-invalid @enderror" autofocus>
                        @include('partials.field-error', ['name' => 'email'])
                    </div>
                    <div class="form-field">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="auth-label !mb-0">Password</label>
                            <a href="#" class="text-xs font-medium text-brand-dark hover:text-brand">Forgot password?</a>
                        </div>
                        <input id="password" type="password" name="password" placeholder="Enter your password" class="auth-input @error('password') is-invalid @enderror">
                        @include('partials.field-error', ['name' => 'password'])
                    </div>
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="remember" value="1" class="checkout-checkbox" @checked(old('remember'))>
                        <span class="text-sm text-gray-600">Remember me</span>
                    </label>
                    <button type="submit" class="auth-submit">Sign in</button>
                </form>

                <p class="mt-8 text-center text-sm text-gray-500">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="font-semibold text-brand-dark hover:text-brand">Create account</a>
                </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
