<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
}
