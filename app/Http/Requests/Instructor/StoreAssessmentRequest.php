<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'chapter_id' => ['nullable', 'exists:chapters,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:mcq,essay'],
            'description' => ['nullable', 'string', 'max:1000'],
            'pass_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'Please select a course.',
            'title.required' => 'Assessment title is required.',
            'type.required' => 'Assessment type is required.',
            'type.in' => 'Assessment type must be MCQ or Essay.',
        ];
    }
}
