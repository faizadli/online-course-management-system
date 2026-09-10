<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'level' => ['nullable', 'string', 'in:Beginner,Intermediate,Advanced'],
            'language' => ['nullable', 'string', 'max:50'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Course title is required.',
            'category_id.exists' => 'Selected category is invalid.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price cannot be negative.',
        ];
    }
}
