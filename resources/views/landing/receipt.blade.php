@extends('layouts.landing')

@section('title', 'Payment Receipt — SkillForge')

@section('content')

<section class="checkout-page pt-20 lg:pt-24 pb-16 lg:pb-20 bg-white">
    <div class="page-container max-w-3xl">
        <div class="mt-4">@include('partials.dashboard.alerts')</div>

        <div class="checkout-panel !p-6 sm:!p-8 reveal">
            <div class="text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-brand-light flex items-center justify-center">
                    <svg class="w-7 h-7 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="font-display text-2xl font-bold text-gray-900">Payment successful</h1>
                <p class="mt-1 text-sm text-gray-500">Receipt <span class="font-semibold text-gray-700" id="receipt-reference">{{ $reference }}</span></p>
            </div>

            <div class="mt-8 divide-y divide-gray-100 border-t border-b border-gray-100">
                @foreach($payments as $payment)
                <div class="py-4 flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $payment->enrolment->course->title }}</p>
                        <p class="text-xs text-gray-400">{{ $payment->enrolment->course->instructor?->name }} · Paid {{ $payment->paid_at?->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-4 shrink-0">
                        <span class="text-sm font-semibold text-gray-900 tabular-nums">${{ number_format($payment->amount, 2) }}</span>
                        <a href="{{ route('dashboard.student.courses.show', $payment->enrolment->course) }}" class="text-sm font-semibold text-brand-dark hover:text-brand">Start learning →</a>
                    </div>
                </div>
                @endforeach
            </div>

            <dl class="mt-6 grid grid-cols-2 gap-y-2 text-sm">
                <dt class="text-gray-500">Payment method</dt>
                <dd class="text-right font-medium text-gray-900">
                    @php $first = $payments->first(); @endphp
                    {{ $first->method === 'card' ? ($first->card_brand.' •••• '.$first->card_last4) : ucfirst($first->method) }}
                </dd>
                <dt class="text-gray-500">Status</dt>
                <dd class="text-right font-medium text-brand-dark">Paid</dd>
                <dt class="text-gray-900 font-semibold">Total</dt>
                <dd class="text-right font-bold text-gray-900">${{ number_format($total, 2) }}</dd>
            </dl>

            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('dashboard.student.courses.index') }}" class="checkout-cta flex-1">Go to my courses</a>
                <a href="{{ route('courses') }}" class="course-btn-secondary flex-1">Browse more courses</a>
            </div>
        </div>
    </div>
</section>

@endsection
