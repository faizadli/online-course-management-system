<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::query()->count(),
            'new_users_this_month' => User::query()->where('created_at', '>=', now()->startOfMonth())->count(),
            'students' => User::query()->where('role', UserRole::Student)->count(),
            'instructors' => User::query()->where('role', UserRole::Instructor)->count(),
            'published_courses' => Course::query()->where('status', 'published')->count(),
            'draft_courses' => Course::query()->where('status', 'draft')->count(),
            'total_enrolments' => Course::query()->sum('enrolments_count'),
            'total_revenue' => Course::query()->where('status', 'published')->sum('price'),
            'categories' => Category::query()->count(),
        ];

        $topCourses = Course::query()
            ->with('instructor')
            ->orderByDesc('enrolments_count')
            ->take(5)
            ->get();

        $categoryBreakdown = Category::query()
            ->withCount('courses')
            ->orderByDesc('courses_count')
            ->get();

        return view('dashboard.admin.reports.index', compact('stats', 'topCourses', 'categoryBreakdown'));
    }
}
