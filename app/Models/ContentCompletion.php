<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentCompletion extends Model
{
    protected $fillable = [
        'enrolment_id',
        'chapter_content_id',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function enrolment(): BelongsTo
    {
        return $this->belongsTo(Enrolment::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(ChapterContent::class, 'chapter_content_id');
    }
}
