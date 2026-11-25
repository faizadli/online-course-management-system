@extends('layouts.landing')

@section('title', 'Checkout — OCMS')

@section('content')

@php
$selectedMethod = old('method', 'card');
@endphp

<section class="checkout-page pt-20 lg:pt-24 pb-16 lg:pb-20 bg-white">
    <div class="page-container max-w-6xl">
        {{-- Step indicator --}}
        <div class="checkout-steps reveal">
            <div class="checkout-steps-track">
                <div class="checkout-steps-progress" style="width: 100%"></div>
            </div>
            <div class="checkout-steps-list">
                <div class="checkout-steps-item is-done">
                    <span class="checkout-steps-num">1</span>
                    <span>Cart</span>
                </div>
                <div class="checkout-steps-item is-current">
                    <span class="checkout-steps-num">2</span>
                    <span>Payment</span>
                </div>
            </div>
        </div>

        <div class="mt-8">@include('partials.dashboard.alerts')</div>

        <form action="{{ route('checkout.pay') }}" method="post" id="checkout-form" novalidate class="grid lg:grid-cols-5 gap-8 lg:gap-12 mt-6 items-start">
            @csrf
            <input type="hidden" name="method" id="payment-method-input" value="{{ $total > 0 ? $selectedMethod : 'free' }}">

            {{-- Payment --}}
            <div class="lg:col-span-3 reveal">
                <div class="mb-8">
                    <h1 class="font-display text-2xl sm:text-[1.75rem] font-bold text-gray-900 tracking-tight">Payment</h1>
                    <p class="mt-1.5 text-sm text-gray-500">All transactions are secure and encrypted.</p>
                </div>

                @if($total > 0)
                <div class="checkout-panel">
                    <h2 class="checkout-panel-title">Payment method</h2>

                    <div id="payment-methods" class="divide-y divide-gray-100">
                        {{-- Credit Card --}}
                        <div class="payment-method {{ $selectedMethod === 'card' ? 'is-open' : '' }}" data-method="card">
                            <button type="button" class="payment-method-header">
                                <span class="flex items-center gap-3">
                                    <span class="payment-method-radio"></span>
                                    <span class="text-sm font-medium text-gray-900">Credit or debit card</span>
                                </span>
                                <img src="{{ asset('images/payments/visa-mastercard.png') }}" alt="Visa and Mastercard" class="payment-logo-img payment-logo-img-combo">
                            </button>
                            <div class="payment-method-body">
                                <div class="grid sm:grid-cols-2 gap-x-4 gap-y-5">
                                    <div class="sm:col-span-2">
                                        <label for="card-number" class="checkout-label">Card number</label>
                                        <input id="card-number" name="card_number" type="text" inputmode="numeric" placeholder="4242 4242 4242 4242" class="checkout-input @error('card_number') is-invalid @enderror" autocomplete="cc-number" maxlength="23">
                                        @include('partials.field-error', ['name' => 'card_number'])
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="card-name" class="checkout-label">Name on card</label>
                                        <input id="card-name" name="card_name" type="text" value="{{ old('card_name', auth()->user()->name) }}" placeholder="Full name" class="checkout-input @error('card_name') is-invalid @enderror" autocomplete="cc-name">
                                        @include('partials.field-error', ['name' => 'card_name'])
                                    </div>
                                    <div>
                                        <label for="card-expiry" class="checkout-label">Expiry date</label>
                                        <input id="card-expiry" name="card_expiry" type="text" inputmode="numeric" value="{{ old('card_expiry') }}" placeholder="MM / YY" class="checkout-input @error('card_expiry') is-invalid @enderror" autocomplete="cc-exp" maxlength="7">
                                        @include('partials.field-error', ['name' => 'card_expiry'])
                                    </div>
                                    <div>
                                        <label for="card-cvv" class="checkout-label">Security code</label>
                                        <input id="card-cvv" name="card_cvc" type="text" inputmode="numeric" placeholder="CVC" class="checkout-input @error('card_cvc') is-invalid @enderror" autocomplete="cc-csc" maxlength="4">
                                        @include('partials.field-error', ['name' => 'card_cvc'])
                                    </div>
                                </div>
                                <p class="mt-4 text-xs text-gray-400">Demo gateway — use test card <strong class="text-gray-600">4242 4242 4242 4242</strong> with any future expiry date and any CVC. Card <strong class="text-gray-600">4000 0000 0000 0002</strong> simulates a declined payment.</p>
                            </div>
                        </div>

                        {{-- PayPal --}}
                        <div class="payment-method {{ $selectedMethod === 'paypal' ? 'is-open' : '' }}" data-method="paypal">
                            <button type="button" class="payment-method-header">
                                <span class="flex items-center gap-3">
                                    <span class="payment-method-radio"></span>
                                    <span class="text-sm font-medium text-gray-900">PayPal</span>
                                </span>
                                <img src="{{ asset('images/payments/paypal.png') }}" alt="PayPal" class="payment-logo-img payment-logo-img-paypal">
                            </button>
                            <div class="payment-method-body">
                                <p class="text-sm text-gray-500">Pay with your PayPal balance. In this demo the payment is approved instantly in the sandbox.</p>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="checkout-panel">
                    <h2 class="checkout-panel-title">Free enrolment</h2>
                    <p class="text-sm text-gray-500">The selected course is free. Confirm to enrol — no payment details required.</p>
                </div>
                @endif

                <label class="mt-6 flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="terms" value="1" id="terms-checkbox" @checked(old('terms', true)) class="checkout-checkbox mt-0.5">
                    <span class="text-sm text-gray-500 leading-relaxed">
                        I agree to the
                        <a href="#" class="text-brand-dark font-medium underline underline-offset-2 hover:text-brand">Terms of Service</a>
                        and
                        <a href="#" class="text-brand-dark font-medium underline underline-offset-2 hover:text-brand">Privacy Policy</a>.
                    </span>
                </label>

                <button type="submit" id="pay-button" class="checkout-cta mt-6 w-full">
                    {{ $total > 0 ? 'Pay $'.number_format($total, 2) : 'Confirm enrolment' }}
                </button>

                <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-gray-400">
                    <svg class="w-3.5 h-3.5 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Secured by 256-bit SSL encryption
                </p>
            </div>

            {{-- Order summary --}}
            <aside class="lg:col-span-2 reveal lg:sticky lg:top-24">
                <div class="checkout-order">
                    <h2 class="checkout-order-title">Order summary</h2>
                    <p class="checkout-order-meta">{{ $courses->count() }} {{ \Illuminate\Support\Str::plural('course', $courses->count()) }}</p>

                    <div class="checkout-order-items">
                        @foreach($courses as $course)
                        <div class="checkout-order-item">
                            <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="checkout-order-thumb">
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-medium uppercase tracking-wide text-brand-dark">{{ $course->category?->name ?? 'General' }}</p>
                                <p class="mt-0.5 text-sm font-medium text-gray-900 leading-snug">{{ $course->title }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $course->contents_count }} lessons</p>
                            </div>
                            <span class="text-sm font-medium text-gray-900 tabular-nums">{{ $course->price_label }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="checkout-order-lines">
                        <div class="checkout-order-line">
                            <span>Subtotal</span>
                            <span class="tabular-nums">${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="checkout-order-line">
                            <span>Tax</span>
                            <span class="tabular-nums">$0.00</span>
                        </div>
                    </div>

                    <div class="checkout-order-total">
                        <span>Total</span>
                        <span class="tabular-nums">${{ number_format($total, 2) }}</span>
                    </div>
                </div>
            </aside>
        </form>
    </div>
</section>

@endsection
