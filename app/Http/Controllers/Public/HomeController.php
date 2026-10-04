<?php

namespace App\Http\Controllers\Public;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrolment;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featured = $this->publishedCourses()
            ->orderByDesc('enrolments_count')
            ->limit(6)
            ->get();

        $stats = [
            'courses' => Course::query()->where('status', 'published')->count(),
            'learners' => User::query()->where('role', UserRole::Student)->count(),
            'instructors' => User::query()->where('role', UserRole::Instructor)->where('is_active', true)->count(),
            'categories' => Category::query()->where('is_active', true)->count(),
            'enrolments' => Enrolment::query()->count(),
        ];

        return view('landing.index', [
            'featuredCourses' => $featured,
            'stats' => $stats,
        ]);
    }

    public function courses(): View
    {
        return view('landing.courses', [
            'courses' => $this->publishedCourses()->latest()->get(),
            'categories' => Category::query()
                ->where('is_active', true)
                ->whereHas('courses', fn ($q) => $q->where('status', 'published'))
                ->orderBy('name')
                ->get(),
        ]);
    }

    private function publishedCourses()
    {
        return Course::query()
            ->with(['instructor', 'category'])
            ->withCount('contents')
            ->where('status', 'published');
    }
}
