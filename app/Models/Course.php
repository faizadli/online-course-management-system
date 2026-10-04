<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;

class Course extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category_id',
        'instructor_id',
        'status',
        'price',
        'level',
        'language',
        'thumbnail',
        'short_description',
        'description',
        'enrolments_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'enrolments_count' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('sort_order');
    }

    public function contents(): HasManyThrough
    {
        return $this->hasManyThrough(ChapterContent::class, Chapter::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }

    /**
     * Thumbnail URL: uploaded file, an absolute URL, or a neutral placeholder.
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail) {
            if (str_starts_with($this->thumbnail, 'http')) {
                return $this->thumbnail;
            }

            return Storage::disk('public')->url($this->thumbnail);
        }

        return 'https://images.unsplash.com/photo-1501504905252-473c47e087f8?w=800&h=500&fit=crop';
    }

    /**
     * Minimum requirements that must be met before a course can be published
     * (title, description, thumbnail, >= 1 chapter, >= 1 content item).
     *
     * @return array<string, bool>
     */
    public function publishChecklist(): array
    {
        return [
            'Course title' => filled($this->title),
            'Description' => filled($this->description) || filled($this->short_description),
            'Thumbnail image' => filled($this->thumbnail),
            'At least one chapter' => $this->chapters()->exists(),
            'At least one content item' => $this->contents()->exists(),
        ];
    }

    /** @return array<int, string> labels of unmet requirements */
    public function missingPublishRequirements(): array
    {
        return collect($this->publishChecklist())
            ->reject(fn (bool $met) => $met)
            ->keys()
            ->all();
    }

    /** Marketing badge derived from real data (no hard-coded values). */
    public function getBadgeAttribute(): ?string
    {
        if ($this->enrolments_count >= 3) {
            return 'Popular';
        }

        if ($this->created_at && $this->created_at->gt(now()->subDays(30))) {
            return 'New';
        }

        return null;
    }

    public function getBadgeColorAttribute(): string
    {
        return match ($this->badge) {
            'Popular' => 'bg-orange-500',
            'New' => 'bg-brand-dark',
            default => '',
        };
    }

    public function getPriceLabelAttribute(): string
    {
        return (float) $this->price <= 0 ? 'Free' : '$'.rtrim(rtrim(number_format((float) $this->price, 2), '0'), '.');
    }

    public function totalMinutes(): int
    {
        return (int) $this->contents()->sum('duration_minutes');
    }
}
