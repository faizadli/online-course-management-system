<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Support\AdminPagination;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $perPage = AdminPagination::perPage($request);

        $courses = Course::query()
            ->with(['category', 'instructor'])
            ->when($status && in_array($status, ['published', 'draft', 'archived'], true), fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhereHas('instructor', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $counts = [
            'all' => Course::query()->count(),
            'published' => Course::query()->where('status', 'published')->count(),
            'draft' => Course::query()->where('status', 'draft')->count(),
            'archived' => Course::query()->where('status', 'archived')->count(),
        ];

        $categories = Category::query()->orderBy('name')->get();

        return view('dashboard.admin.courses.index', compact('courses', 'counts', 'status', 'search', 'categories'));
    }
}
