<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterInstructorRequest;
use App\Http\Requests\Auth\RegisterStudentRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function showLogin(): View
    {
        return view('landing.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $user = $this->authService->login(
            $request->validated('email'),
            $request->validated('password'),
            $request->boolean('remember')
        );

        return redirect()->intended($this->authService->redirectPathFor($user));
    }

    public function logout(): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    public function showRegister(): View
    {
        return view('landing.register');
    }

    public function showStudentRegister(): View
    {
        return view('landing.register-student');
    }

    public function registerStudent(RegisterStudentRequest $request): RedirectResponse
    {
        $this->authService->registerStudent($request->validated());

        return redirect()
            ->route('login')
            ->with('success', 'Student account created successfully. Please sign in.');
    }

    public function showInstructorRegister(): View
    {
        return view('landing.register-instructor');
    }

    public function registerInstructor(RegisterInstructorRequest $request): RedirectResponse
    {
        $this->authService->registerInstructor($request->validated());

        return redirect()
            ->route('login')
            ->with('success', 'Instructor account created successfully. Please sign in.');
    }
}
