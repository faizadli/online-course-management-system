<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreChapterContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:video,text,pdf'],
            'body' => ['nullable', 'string', 'max:10000'],
            'url' => ['nullable', 'string', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:600'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('type');

            if (in_array($type, ['video', 'pdf'], true) && blank($this->input('url'))) {
                $validator->errors()->add('url', 'URL is required for video and PDF content.');
            }

            if ($type === 'text' && blank($this->input('body'))) {
                $validator->errors()->add('body', 'Content body is required for text lessons.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Content title is required.',
            'type.required' => 'Content type is required.',
            'type.in' => 'Content type must be video, text, or pdf.',
        ];
    }
}
