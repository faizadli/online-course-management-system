<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ChapterContent extends Model
{
    public const TYPES = ['video', 'text', 'pdf'];

    protected $fillable = [
        'chapter_id',
        'title',
        'type',
        'body',
        'url',
        'file_path',
        'is_free_preview',
        'duration_minutes',
        'sort_order',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'sort_order' => 'integer',
        'is_free_preview' => 'boolean',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /** Public URL of the uploaded file, or the external URL. */
    public function getResourceUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        return $this->url ?: null;
    }

    /** Embeddable iframe URL for YouTube / Vimeo links, otherwise null. */
    public function getEmbedUrlAttribute(): ?string
    {
        $url = $this->url;

        if (! $url) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]{11})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'video' => 'Video',
            'text' => 'Blog / Text',
            'pdf' => 'PDF',
            default => ucfirst((string) $this->type),
        };
    }
}
