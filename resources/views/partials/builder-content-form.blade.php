{{-- Content form used by the Course Builder.
     Params: $action, $type (video|text|pdf), $uid (unique form id), optional $content (edit mode), $submitLabel --}}
@php
$isEdit = ! empty($content);
$showErrors = old('_form') === $uid;
$err = fn ($field) => $showErrors ? $errors->first($field) : null;
$bodyValue = $showErrors ? old('body') : ($isEdit ? $content->body : '');
@endphp

<form action="{{ $action }}" method="post" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-3" data-builder-form id="form-{{ $uid }}" novalidate>
    @csrf
    @if($isEdit) @method('PUT') @endif
    <input type="hidden" name="_form" value="{{ $uid }}">
    <input type="hidden" name="type" value="{{ $type }}">

    <div class="form-field sm:col-span-2">
        <label class="dashboard-label" for="title-{{ $uid }}">Lesson title</label>
        <input type="text" id="title-{{ $uid }}" name="title" value="{{ $showErrors ? old('title') : ($isEdit ? $content->title : '') }}" class="dashboard-input {{ $err('title') ? 'is-invalid' : '' }}" placeholder="e.g. Introduction to wireframing">
        @if($err('title'))<p class="field-error">{{ $err('title') }}</p>@endif
    </div>

    @if($type === 'video')
    <div class="form-field sm:col-span-2">
        <label class="dashboard-label" for="file-{{ $uid }}">Upload video <span class="text-gray-400 font-normal">(MP4, WebM, OGG, MOV · max 200 MB)</span></label>
        <input type="file" id="file-{{ $uid }}" name="file" accept="video/mp4,video/webm,video/ogg,video/quicktime" class="dashboard-input {{ $err('file') ? 'is-invalid' : '' }}">
        @if($err('file'))<p class="field-error">{{ $err('file') }}</p>@endif
        @if($isEdit && $content->file_path)<p class="mt-1 text-xs text-gray-500">Current file: {{ basename($content->file_path) }} (upload a new one to replace it)</p>@endif
    </div>
    <div class="form-field sm:col-span-2">
        <label class="dashboard-label" for="url-{{ $uid }}">…or link a video <span class="text-gray-400 font-normal">(YouTube, Vimeo or direct URL)</span></label>
        <input type="text" id="url-{{ $uid }}" name="url" value="{{ $showErrors ? old('url') : ($isEdit ? $content->url : '') }}" class="dashboard-input {{ $err('url') ? 'is-invalid' : '' }}" placeholder="https://www.youtube.com/watch?v=...">
        @if($err('url'))<p class="field-error">{{ $err('url') }}</p>@endif
    </div>
    <div class="form-field sm:col-span-2">
        <label class="dashboard-label" for="body-{{ $uid }}">Description <span class="text-gray-400 font-normal">(optional)</span></label>
        <textarea id="body-{{ $uid }}" name="body" rows="2" class="dashboard-input" placeholder="Short summary shown under the video">{{ $bodyValue }}</textarea>
    </div>

    @elseif($type === 'pdf')
    <div class="form-field sm:col-span-2">
        <label class="dashboard-label" for="file-{{ $uid }}">Upload PDF <span class="text-gray-400 font-normal">(max 200 MB)</span></label>
        <input type="file" id="file-{{ $uid }}" name="file" accept="application/pdf" class="dashboard-input {{ $err('file') ? 'is-invalid' : '' }}">
        @if($err('file'))<p class="field-error">{{ $err('file') }}</p>@endif
        @if($isEdit && $content->file_path)<p class="mt-1 text-xs text-gray-500">Current file: {{ basename($content->file_path) }} (upload a new one to replace it)</p>@endif
    </div>
    <div class="form-field sm:col-span-2">
        <label class="dashboard-label" for="url-{{ $uid }}">…or link a PDF URL</label>
        <input type="text" id="url-{{ $uid }}" name="url" value="{{ $showErrors ? old('url') : ($isEdit ? $content->url : '') }}" class="dashboard-input {{ $err('url') ? 'is-invalid' : '' }}" placeholder="https://example.com/handout.pdf">
        @if($err('url'))<p class="field-error">{{ $err('url') }}</p>@endif
    </div>

    @else
    <div class="form-field sm:col-span-2">
        <label class="dashboard-label">Lesson content</label>
        <div class="rte" data-rte>
            <div class="rte-toolbar" role="toolbar" aria-label="Formatting">
                <button type="button" data-cmd="bold" title="Bold"><strong>B</strong></button>
                <button type="button" data-cmd="italic" title="Italic"><em>I</em></button>
                <button type="button" data-cmd="formatBlock" data-value="h2" title="Heading">H2</button>
                <button type="button" data-cmd="formatBlock" data-value="h3" title="Subheading">H3</button>
                <button type="button" data-cmd="insertUnorderedList" title="Bullet list">• List</button>
                <button type="button" data-cmd="insertOrderedList" title="Numbered list">1. List</button>
                <button type="button" data-cmd="formatBlock" data-value="blockquote" title="Quote">“ ”</button>
                <button type="button" data-action="link" title="Insert link">Link</button>
                <button type="button" data-action="image" title="Insert image">Image</button>
                <button type="button" data-action="table" title="Insert table">Table</button>
            </div>
            <div class="rte-area lesson-prose" contenteditable="true" data-rte-area>{!! \App\Support\HtmlSanitizer::clean($bodyValue) !!}</div>
            <textarea name="body" class="hidden" data-rte-input>{{ $bodyValue }}</textarea>
        </div>
        @if($err('body'))<p class="field-error">{{ $err('body') }}</p>@endif
    </div>
    @endif

    <div class="form-field">
        <label class="dashboard-label" for="duration-{{ $uid }}">Duration (min)</label>
        <input type="number" id="duration-{{ $uid }}" name="duration_minutes" value="{{ $showErrors ? old('duration_minutes') : ($isEdit ? $content->duration_minutes : 10) }}" min="0" class="dashboard-input">
    </div>
    <div class="form-field flex items-end">
        <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer pb-2.5">
            <input type="checkbox" name="is_free_preview" value="1" class="checkout-checkbox" @checked($showErrors ? old('is_free_preview') : ($isEdit && $content->is_free_preview))>
            Free preview lesson
        </label>
    </div>

    <div class="sm:col-span-2 flex items-center gap-3">
        <button type="submit" class="px-4 py-2 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand transition-colors">{{ $submitLabel ?? 'Add Content' }}</button>
        @if($isEdit)
        <a href="{{ route('dashboard.instructor.builder.show', $course) }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700">Cancel</a>
        @endif
    </div>
</form>
