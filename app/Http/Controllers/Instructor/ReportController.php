<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Course;
use App\Models\Enrolment;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $instructorId = auth()->id();
        $courseIds = Course::query()->where('instructor_id', $instructorId)->pluck('id');

        $stats = [
            'total_courses' => $courseIds->count(),
            'published_courses' => Course::query()->where('instructor_id', $instructorId)->where('status', 'published')->count(),
            'total_enrolments' => Enrolment::query()->whereIn('course_id', $courseIds)->count(),
            'active_students' => Enrolment::query()->whereIn('course_id', $courseIds)->where('status', 'active')->distinct('student_id')->count('student_id'),
            'completed_enrolments' => Enrolment::query()->whereIn('course_id', $courseIds)->where('status', 'completed')->count(),
            'revenue' => Enrolment::query()->whereIn('course_id', $courseIds)->sum('amount_paid'),
            'assessments' => Assessment::query()->whereIn('course_id', $courseIds)->count(),
            'avg_score' => round((float) AssessmentSubmission::query()
                ->whereHas('assessment', fn ($q) => $q->whereIn('course_id', $courseIds))
                ->whereNotNull('score')
                ->avg('score'), 1),
        ];

        $coursePerformance = Course::query()
            ->where('instructor_id', $instructorId)
            ->withCount(['enrolments', 'assessments', 'chapters'])
            ->orderByDesc('enrolments_count')
            ->get();

        $assessmentStats = Assessment::query()
            ->whereIn('course_id', $courseIds)
            ->with('course')
            ->withCount('submissions')
            ->withAvg('submissions', 'score')
            ->orderByDesc('submissions_count')
            ->take(8)
            ->get();

        return view('dashboard.instructor.reports.index', compact('stats', 'coursePerformance', 'assessmentStats'));
    }
}
