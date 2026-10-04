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
        'rubric',
        'explanation',
        'points',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'rubric' => 'array',
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    /**
     * Rubric criteria used by the AI scoring module. When the instructor did
     * not define explicit criteria, a sensible default rubric is generated so
     * that the model answer can still be used for evaluation.
     *
     * @return array<int, array{criteria: string, description: string, weight: int}>
     */
    public function effectiveRubric(): array
    {
        $rubric = collect($this->rubric ?? [])
            ->filter(fn ($row) => filled($row['criteria'] ?? null))
            ->map(fn ($row) => [
                'criteria' => (string) $row['criteria'],
                'description' => (string) ($row['description'] ?? ''),
                'weight' => max(0, (int) ($row['weight'] ?? 0)),
            ])
            ->values();

        if ($rubric->isNotEmpty() && $rubric->sum('weight') > 0) {
            return $rubric->all();
        }

        return [
            ['criteria' => 'Correctness of Concept', 'description' => 'The answer is factually accurate and consistent with the model answer.', 'weight' => 40],
            ['criteria' => 'Completeness', 'description' => 'The answer covers all key points expected from the model answer.', 'weight' => 35],
            ['criteria' => 'Clarity', 'description' => 'The answer is well organised, coherent and clearly written.', 'weight' => 25],
        ];
    }
}
