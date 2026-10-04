<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrolment extends Model
{
    protected $fillable = [
        'course_id',
        'student_id',
        'status',
        'amount_paid',
        'progress',
        'enrolled_at',
        'completed_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'progress' => 'integer',
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(ContentCompletion::class);
    }

    public function hasCompleted(ChapterContent $content): bool
    {
        return $this->completions()->where('chapter_content_id', $content->id)->exists();
    }

    /**
     * Recalculate the completion percentage from completed lessons and
     * submitted (published) assessments, and update the status accordingly.
     */
    public function recalculateProgress(): int
    {
        $course = $this->course;

        $contentIds = ChapterContent::query()
            ->whereIn('chapter_id', $course->chapters()->pluck('id'))
            ->pluck('id');

        $assessmentIds = $course->assessments()->where('status', 'published')->pluck('id');

        $total = $contentIds->count() + $assessmentIds->count();

        if ($total === 0) {
            return $this->progress;
        }

        $doneLessons = $this->completions()->whereIn('chapter_content_id', $contentIds)->count();
        $doneAssessments = AssessmentSubmission::query()
            ->where('student_id', $this->student_id)
            ->whereIn('assessment_id', $assessmentIds)
            ->count();

        $progress = (int) min(100, round((($doneLessons + $doneAssessments) / $total) * 100));

        $this->update([
            'progress' => $progress,
            'status' => $progress >= 100 ? 'completed' : 'active',
            'completed_at' => $progress >= 100 ? ($this->completed_at ?? now()) : null,
        ]);

        return $progress;
    }
}
