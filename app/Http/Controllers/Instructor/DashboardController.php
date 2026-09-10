<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Course;
use App\Models\Enrolment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $instructorId = auth()->id();

        $courseIds = Course::query()
            ->where('instructor_id', $instructorId)
            ->pluck('id');

        $stats = [
            'total_courses' => $courseIds->count(),
            'published_courses' => Course::query()->where('instructor_id', $instructorId)->where('status', 'published')->count(),
            'draft_courses' => Course::query()->where('instructor_id', $instructorId)->where('status', 'draft')->count(),
            'total_students' => Enrolment::query()->whereIn('course_id', $courseIds)->distinct('student_id')->count('student_id'),
            'pending_reviews' => AssessmentSubmission::query()
                ->whereHas('assessment', fn ($q) => $q->whereIn('course_id', $courseIds))
                ->where('status', 'pending')
                ->count(),
            'total_enrolments' => Enrolment::query()->whereIn('course_id', $courseIds)->count(),
        ];

        $courses = Course::query()
            ->where('instructor_id', $instructorId)
            ->withCount('chapters')
            ->latest()
            ->take(5)
            ->get();

        $topCourses = Course::query()
            ->where('instructor_id', $instructorId)
            ->orderByDesc('enrolments_count')
            ->take(3)
            ->get();

        $recentSubmissions = AssessmentSubmission::query()
            ->with(['student', 'assessment.course'])
            ->whereHas('assessment', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->latest('submitted_at')
            ->take(5)
            ->get();

        $monthlyEnrolments = collect(range(5, 0))->map(function ($monthsAgo) use ($courseIds) {
            $date = now()->subMonths($monthsAgo);

            return [
                'label' => $date->format('M'),
                'count' => Enrolment::query()
                    ->whereIn('course_id', $courseIds)
                    ->whereYear('enrolled_at', $date->year)
                    ->whereMonth('enrolled_at', $date->month)
                    ->count(),
            ];
        });

        return view('dashboard.instructor.index', compact(
            'stats',
            'courses',
            'topCourses',
            'recentSubmissions',
            'monthlyEnrolments'
        ));
    }
}
