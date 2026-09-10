<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentQuestion extends Model
{
    protected $fillable = [
        'assessment_id',
        'question',
        'options',
        'model_answer',
        'points',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }
}
