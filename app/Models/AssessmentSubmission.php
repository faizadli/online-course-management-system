<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentSubmission extends Model
{
    protected $fillable = [
        'assessment_id',
        'student_id',
        'status',
        'answers',
        'score',
        'feedback',
        'submitted_at',
        'graded_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'decimal:2',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
