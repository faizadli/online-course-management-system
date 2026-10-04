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
            'body' => ['nullable', 'string', 'max:50000'],
            'url' => ['nullable', 'string', 'max:500', 'url'],
            'file' => ['nullable', 'file', 'max:204800'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:600'],
            'is_free_preview' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('type');
            $file = $this->file('file');
            $isUpdate = $this->route('content') !== null;
            $existing = $this->route('content');

            if ($type === 'video') {
                $hasExisting = $isUpdate && ($existing->file_path || $existing->url);

                if (blank($this->input('url')) && ! $file && ! $hasExisting) {
                    $validator->errors()->add('url', 'Upload a video file or paste a YouTube, Vimeo or direct video URL.');
                }

                if ($file && ! in_array(strtolower($file->getClientOriginalExtension()), ['mp4', 'webm', 'ogg', 'mov'], true)) {
                    $validator->errors()->add('file', 'Video must be an MP4, WebM, OGG or MOV file.');
                }
            }

            if ($type === 'pdf') {
                $hasExisting = $isUpdate && ($existing->file_path || $existing->url);

                if ($file && strtolower($file->getClientOriginalExtension()) !== 'pdf') {
                    $validator->errors()->add('file', 'Document must be a PDF file.');
                }

                if (! $file && blank($this->input('url')) && ! $hasExisting) {
                    $validator->errors()->add('file', 'Upload a PDF file or provide a PDF URL.');
                }
            }

            if ($type === 'text' && blank(trim(strip_tags((string) $this->input('body'), '<img><table>')))) {
                $validator->errors()->add('body', 'Lesson content is required for blog / text lessons.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Content title is required.',
            'type.required' => 'Content type is required.',
            'type.in' => 'Content type must be video, text, or pdf.',
            'url.url' => 'Please enter a valid URL (including https://).',
            'file.max' => 'The uploaded file may not be larger than 200 MB.',
        ];
    }
}
