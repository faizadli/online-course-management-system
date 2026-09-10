<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Enrolment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $studentId = auth()->id();

        $enrolments = Enrolment::query()
            ->with(['course.instructor', 'course.category'])
            ->where('student_id', $studentId)
            ->latest('enrolled_at')
            ->get();

        $enrolledCourseIds = $enrolments->pluck('course_id');

        $stats = [
            'enrolled' => $enrolments->count(),
            'completed' => $enrolments->where('status', 'completed')->count(),
            'in_progress' => $enrolments->where('status', 'active')->count(),
            'pending_assessments' => Assessment::query()
                ->whereIn('course_id', $enrolledCourseIds)
                ->where('status', 'published')
                ->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $studentId))
                ->count(),
        ];

        $recentEnrolments = $enrolments->take(4);

        $upcomingAssessments = Assessment::query()
            ->with('course')
            ->whereIn('course_id', $enrolledCourseIds)
            ->where('status', 'published')
            ->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $studentId))
            ->latest()
            ->take(4)
            ->get();

        $recentFeedback = AssessmentSubmission::query()
            ->with(['assessment.course'])
            ->where('student_id', $studentId)
            ->whereIn('status', ['graded', 'ai_graded'])
            ->latest('graded_at')
            ->take(4)
            ->get();

        return view('dashboard.student.index', compact(
            'stats',
            'recentEnrolments',
            'upcomingAssessments',
            'recentFeedback'
        ));
    }
}
