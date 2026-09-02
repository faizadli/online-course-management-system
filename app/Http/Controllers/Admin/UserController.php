<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->query('role');
        $search = $request->query('search');
        $perPage = AdminPagination::perPage($request);

        $users = User::query()
            ->when($role && in_array($role, ['student', 'instructor', 'admin'], true), fn ($q) => $q->where('role', $role))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $counts = [
            'all' => User::query()->count(),
            'student' => User::query()->where('role', UserRole::Student)->count(),
            'instructor' => User::query()->where('role', UserRole::Instructor)->count(),
            'admin' => User::query()->where('role', UserRole::Admin)->count(),
        ];

        return view('dashboard.admin.users.index', compact('users', 'counts', 'role', 'search'));
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? 'User activated successfully.' : 'User deactivated successfully.');
    }
}
