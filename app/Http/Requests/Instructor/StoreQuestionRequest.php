<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:2000'],
            'points' => ['nullable', 'integer', 'min:1', 'max:100'],
            'model_answer' => ['nullable', 'string', 'max:5000'],
            'options' => ['nullable', 'array'],
            'options.*.text' => ['nullable', 'string', 'max:500'],
            'correct_option' => ['nullable', 'integer', 'min:0', 'max:3'],
            'explanation' => ['nullable', 'string', 'max:500'],
            'rubric' => ['nullable', 'array', 'max:8'],
            'rubric.*.criteria' => ['nullable', 'string', 'max:100'],
            'rubric.*.description' => ['nullable', 'string', 'max:300'],
            'rubric.*.weight' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $assessment = $this->route('assessment');

            if ($assessment && $assessment->type === 'mcq') {
                $options = collect($this->input('options', []))
                    ->filter(fn ($option) => filled($option['text'] ?? null));

                if ($options->count() < 2) {
                    $validator->errors()->add('options', 'Add at least two answer options for MCQ.');
                }

                if ($this->input('correct_option') === null) {
                    $validator->errors()->add('correct_option', 'Select the correct answer.');
                }
            }

            if ($assessment && $assessment->type === 'essay') {
                $rubricRows = collect($this->input('rubric', []))
                    ->filter(fn ($row) => filled($row['criteria'] ?? null));

                if ($rubricRows->isEmpty() && blank($this->input('model_answer'))) {
                    $validator->errors()->add('model_answer', 'Provide a model answer or at least one rubric criterion so the AI can score the response.');
                }

                if ($rubricRows->isNotEmpty() && $rubricRows->sum(fn ($row) => (int) ($row['weight'] ?? 0)) !== 100) {
                    $validator->errors()->add('rubric', 'Rubric criteria weights must add up to exactly 100%.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'question.required' => 'Question text is required.',
        ];
    }
}
