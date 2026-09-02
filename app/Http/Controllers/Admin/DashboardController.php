<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::query()->count(),
            'students' => User::query()->where('role', UserRole::Student)->count(),
            'instructors' => User::query()->where('role', UserRole::Instructor)->count(),
            'active_courses' => Course::query()->where('status', 'published')->count(),
            'draft_courses' => Course::query()->where('status', 'draft')->count(),
            'categories' => Category::query()->count(),
            'total_enrolments' => Course::query()->sum('enrolments_count'),
        ];

        $recentUsers = User::query()->latest()->take(5)->get();
        $recentCourses = Course::query()->with(['category', 'instructor'])->latest()->take(5)->get();

        return view('dashboard.admin.index', compact('stats', 'recentUsers', 'recentCourses'));
    }
}
