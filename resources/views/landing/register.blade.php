@extends('layouts.auth')

@section('title', 'Register — OCMS')

@section('content')

<div class="auth-page auth-page-full">
    <div class="auth-shell">
        <div class="auth-panel auth-panel-brand">
            <div class="auth-panel-content">
                <a href="{{ route('home') }}" class="font-display text-2xl font-bold text-white">OCMS</a>
                <h1 class="mt-10 font-display text-3xl sm:text-4xl font-bold text-white leading-tight">Join OCMS</h1>
                <p class="mt-4 text-white/80 leading-relaxed max-w-sm">
                    Choose how you want to use the platform — learn new skills as a student or share your expertise as an instructor.
                </p>
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
                    <h2 class="mt-6 lg:mt-0 font-display text-2xl font-bold text-gray-900">Create account</h2>
                    <p class="mt-2 text-sm text-gray-500">Select your account type to continue registration.</p>

                    <div class="mt-8 grid gap-4">
                        <a href="{{ route('register.student') }}" class="auth-role-card group">
                            <div class="auth-role-icon">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 group-hover:text-brand-dark transition-colors">Register as Student</p>
                                <p class="mt-1 text-sm text-gray-500">Browse courses, enrol, and track your learning progress.</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-300 group-hover:text-brand-dark shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <a href="{{ route('register.instructor') }}" class="auth-role-card group">
                            <div class="auth-role-icon">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 group-hover:text-brand-dark transition-colors">Register as Instructor</p>
                                <p class="mt-1 text-sm text-gray-500">Create courses, manage content, and assess students.</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-300 group-hover:text-brand-dark shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <p class="mt-8 text-center text-sm text-gray-500">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-semibold text-brand-dark hover:text-brand">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
