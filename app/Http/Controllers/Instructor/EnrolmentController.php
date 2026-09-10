<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrolment;
use App\Support\AdminPagination;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrolmentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $courseId = $request->query('course_id');
        $search = $request->query('search');
        $perPage = AdminPagination::perPage($request);
        $instructorId = auth()->id();

        $courseIds = Course::query()->where('instructor_id', $instructorId)->pluck('id');

        $enrolments = Enrolment::query()
            ->with(['student', 'course'])
            ->whereIn('course_id', $courseIds)
            ->when($status && in_array($status, ['active', 'completed', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('student', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('enrolled_at')
            ->paginate($perPage)
            ->withQueryString();

        $counts = [
            'all' => Enrolment::query()->whereIn('course_id', $courseIds)->count(),
            'active' => Enrolment::query()->whereIn('course_id', $courseIds)->where('status', 'active')->count(),
            'completed' => Enrolment::query()->whereIn('course_id', $courseIds)->where('status', 'completed')->count(),
            'cancelled' => Enrolment::query()->whereIn('course_id', $courseIds)->where('status', 'cancelled')->count(),
        ];

        $courses = Course::query()
            ->where('instructor_id', $instructorId)
            ->orderBy('title')
            ->get();

        return view('dashboard.instructor.enrolments.index', compact(
            'enrolments',
            'counts',
            'status',
            'courseId',
            'search',
            'courses'
        ));
    }
}
