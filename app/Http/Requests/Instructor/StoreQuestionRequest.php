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

            if ($assessment && $assessment->type === 'essay' && blank($this->input('model_answer'))) {
                $validator->errors()->add('model_answer', 'Model answer is recommended for essay grading.');
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
