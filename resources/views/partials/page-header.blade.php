<div class="pt-24 lg:pt-28 pb-12 lg:pb-16 {{ $bgClass ?? 'bg-surface' }}">
    <div class="page-container">
        @if(!empty($eyebrow))
        <p class="text-sm font-semibold uppercase tracking-widest text-brand-dark mb-3 reveal">{{ $eyebrow }}</p>
        @endif
        <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 reveal">{{ $title }}</h1>
        @if(!empty($description))
        <p class="mt-4 text-base sm:text-lg text-gray-500 max-w-2xl leading-relaxed reveal">{{ $description }}</p>
        @endif
    </div>
</div>
