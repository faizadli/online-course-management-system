<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
