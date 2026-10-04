<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
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
            // Status is optional here: publishing goes through the publish checklist.
            'status' => ['nullable', 'in:draft,published,archived'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'level' => ['nullable', 'string', 'in:Beginner,Intermediate,Advanced'],
            'language' => ['nullable', 'string', 'max:50'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Course title is required.',
            'status.in' => 'Invalid course status.',
            'category_id.exists' => 'Selected category is invalid.',
            'thumbnail.image' => 'The thumbnail must be an image file.',
            'thumbnail.max' => 'The thumbnail may not be larger than 3 MB.',
        ];
    }
}
