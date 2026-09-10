<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssessmentSubmission;
use App\Support\AdminPagination;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $perPage = AdminPagination::perPage($request);
        $studentId = auth()->id();

        $submissions = AssessmentSubmission::query()
            ->with(['assessment.course'])
            ->where('student_id', $studentId)
            ->whereIn('status', ['graded', 'ai_graded', 'pending'])
            ->when($status && in_array($status, ['graded', 'ai_graded', 'pending'], true), fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('assessment', fn ($query) => $query->where('title', 'like', "%{$search}%"));
            })
            ->latest('submitted_at')
            ->paginate($perPage)
            ->withQueryString();

        $counts = [
            'all' => AssessmentSubmission::query()->where('student_id', $studentId)->count(),
            'ai_graded' => AssessmentSubmission::query()->where('student_id', $studentId)->where('status', 'ai_graded')->count(),
            'graded' => AssessmentSubmission::query()->where('student_id', $studentId)->where('status', 'graded')->count(),
            'pending' => AssessmentSubmission::query()->where('student_id', $studentId)->where('status', 'pending')->count(),
        ];

        $avgScore = round((float) AssessmentSubmission::query()
            ->where('student_id', $studentId)
            ->whereNotNull('score')
            ->avg('score'), 1);

        return view('dashboard.student.feedback.index', compact('submissions', 'counts', 'status', 'search', 'avgScore'));
    }

    public function show(AssessmentSubmission $submission): View
    {
        abort_unless($submission->student_id === auth()->id(), 403);

        $submission->load(['assessment.course', 'assessment.questions']);

        return view('dashboard.student.feedback.show', compact('submission'));
    }
}
