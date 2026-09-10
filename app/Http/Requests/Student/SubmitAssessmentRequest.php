<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'Please provide your answers before submitting.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $assessment = $this->route('assessment');
            $answers = collect($this->input('answers', []))->filter(fn ($value) => filled($value));

            if ($answers->isEmpty()) {
                $validator->errors()->add('answers', 'Please answer at least one question.');
            }

            if ($assessment && $assessment->type === 'mcq') {
                $questionIds = $assessment->questions()->pluck('id');
                $missing = $questionIds->first(fn ($id) => blank($this->input("answers.{$id}")));

                if ($missing) {
                    $validator->errors()->add('answers', 'Please answer all MCQ questions before submitting.');
                }
            }
        });
    }
}
