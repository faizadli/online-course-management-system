{{-- Renders the body of a lesson (video / blog text / pdf). Expects $content and optional $safeBody --}}
@if($content->type === 'video')
    @php
        $source = $content->resource_url;
        $isDirect = $content->file_path || ($source && preg_match('~\.(mp4|webm|ogg|mov)(\?.*)?$~i', $source));
    @endphp
    @if($content->embed_url)
    <div class="aspect-video w-full rounded-xl overflow-hidden bg-black">
        <iframe src="{{ $content->embed_url }}" class="w-full h-full" allowfullscreen loading="lazy" title="{{ $content->title }}"></iframe>
    </div>
    @elseif($isDirect)
    <video controls preload="metadata" class="w-full rounded-xl bg-black aspect-video" id="lesson-video">
        <source src="{{ $source }}">
        Your browser does not support the video tag.
    </video>
    @elseif($source)
    <div class="p-6 rounded-xl bg-gray-50 text-center">
        <p class="text-sm text-gray-600 mb-3">This video is hosted externally.</p>
        <a href="{{ $source }}" target="_blank" rel="noopener" class="inline-flex px-5 py-2.5 bg-brand-dark text-white text-sm font-semibold rounded-lg hover:bg-brand">Open video ↗</a>
    </div>
    @else
    <p class="text-sm text-gray-500">No video has been attached to this lesson yet.</p>
    @endif
    @if($content->body)
    <div class="mt-5 text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $content->body }}</div>
    @endif

@elseif($content->type === 'text')
    <article class="lesson-prose" id="lesson-text">{!! $safeBody ?? \App\Support\HtmlSanitizer::clean($content->body) !!}</article>

@elseif($content->type === 'pdf')
    @if($content->resource_url)
    <div class="flex items-center justify-between gap-3 mb-3">
        <p class="text-sm text-gray-500">View the document below or download a copy.</p>
        <a href="{{ $content->resource_url }}" download target="_blank" rel="noopener" class="px-4 py-2 border border-gray-200 text-sm font-semibold rounded-lg hover:bg-gray-50">Download PDF</a>
    </div>
    <iframe src="{{ $content->resource_url }}" class="w-full rounded-xl border border-gray-100 h-[70vh] min-h-[28rem]" title="{{ $content->title }}"></iframe>
    @else
    <p class="text-sm text-gray-500">No document has been attached to this lesson yet.</p>
    @endif
@endif
