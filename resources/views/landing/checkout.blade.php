@extends('layouts.landing')

@section('title', 'Checkout — SkillForge')

@section('content')

@php
$orderCourses = [
    ['title' => 'UI/UX Design Fundamentals', 'category' => 'Design', 'image' => 'photo-1561070791-2526d30994b5', 'lessons' => 24, 'hours' => 18, 'price' => 49],
    ['title' => 'Full Stack Web Development', 'category' => 'Tech', 'image' => 'photo-1498050108023-c5249f4df085', 'lessons' => 36, 'hours' => 42, 'price' => 79],
];
$subtotal = 128;
$discount = 10;
$total = 118;
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

        <div class="grid lg:grid-cols-5 gap-8 lg:gap-12 mt-10 lg:mt-12 items-start">
            {{-- Payment --}}
            <div class="lg:col-span-3 reveal">
                <div class="mb-8">
                    <h1 class="font-display text-2xl sm:text-[1.75rem] font-bold text-gray-900 tracking-tight">Payment</h1>
                    <p class="mt-1.5 text-sm text-gray-500">All transactions are secure and encrypted.</p>
                </div>

                <div class="checkout-panel">
                    <h2 class="checkout-panel-title">Payment method</h2>

                    <div id="payment-methods" class="divide-y divide-gray-100">
                        {{-- Credit Card --}}
                        <div class="payment-method is-open" data-method="card">
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
                                        <input id="card-number" type="text" placeholder="1234 5678 9012 3456" class="checkout-input" autocomplete="cc-number">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label for="card-name" class="checkout-label">Name on card</label>
                                        <input id="card-name" type="text" placeholder="Full name" class="checkout-input" autocomplete="cc-name">
                                    </div>
                                    <div>
                                        <label for="card-expiry" class="checkout-label">Expiry date</label>
                                        <input id="card-expiry" type="text" placeholder="MM / YY" class="checkout-input" autocomplete="cc-exp">
                                    </div>
                                    <div>
                                        <label for="card-cvv" class="checkout-label">Security code</label>
                                        <input id="card-cvv" type="text" placeholder="CVC" class="checkout-input" autocomplete="cc-csc">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PayPal --}}
                        <div class="payment-method" data-method="paypal">
                            <button type="button" class="payment-method-header">
                                <span class="flex items-center gap-3">
                                    <span class="payment-method-radio"></span>
                                    <span class="text-sm font-medium text-gray-900">PayPal</span>
                                </span>
                                <img src="{{ asset('images/payments/paypal.png') }}" alt="PayPal" class="payment-logo-img payment-logo-img-paypal">
                            </button>
                            <div class="payment-method-body">
                                <p class="text-sm text-gray-500">You will be redirected to PayPal to complete payment.</p>
                            </div>
                        </div>

                        {{-- Bank Transfer --}}
                        <div class="payment-method" data-method="bank">
                            <button type="button" class="payment-method-header">
                                <span class="flex items-center gap-3">
                                    <span class="payment-method-radio"></span>
                                    <span class="text-sm font-medium text-gray-900">Bank transfer</span>
                                </span>
                                <img src="{{ asset('images/payments/bank.svg') }}" alt="Bank transfer" class="payment-logo-img payment-logo-img-lg">
                            </button>
                            <div class="payment-method-body">
                                <p class="text-sm text-gray-500">Course access is granted after payment confirmation within 1–2 business days.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <label class="mt-6 flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" checked class="checkout-checkbox mt-0.5">
                    <span class="text-sm text-gray-500 leading-relaxed">
                        I agree to the
                        <a href="#" class="text-brand-dark font-medium underline underline-offset-2 hover:text-brand">Terms of Service</a>
                        and
                        <a href="#" class="text-brand-dark font-medium underline underline-offset-2 hover:text-brand">Privacy Policy</a>.
                    </span>
                </label>

                <button type="button" class="checkout-cta mt-6">
                    Pay ${{ $total }}
                </button>

                <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-gray-400">
                    <svg class="w-3.5 h-3.5 text-brand-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Secured by 256-bit SSL encryption
                </p>

                <div class="payment-brands mt-5 pt-5 border-t border-gray-100">
                    <div class="payment-brands-list">
                        <img src="{{ asset('images/payments/visa-mastercard.png') }}" alt="Visa and Mastercard" class="payment-brand-logo payment-brand-logo-combo">
                        <img src="{{ asset('images/payments/paypal.png') }}" alt="PayPal" class="payment-brand-logo payment-brand-logo-paypal">
                    </div>
                </div>
            </div>

            {{-- Order summary --}}
            <aside class="lg:col-span-2 reveal lg:sticky lg:top-24">
                <div class="checkout-order">
                    <h2 class="checkout-order-title">Order summary</h2>
                    <p class="checkout-order-meta">{{ count($orderCourses) }} courses</p>

                    <div class="checkout-order-items">
                        @foreach($orderCourses as $course)
                        <div class="checkout-order-item">
                            <img src="https://images.unsplash.com/{{ $course['image'] }}?w=120&h=80&fit=crop"
                                alt="{{ $course['title'] }}"
                                class="checkout-order-thumb">
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-medium uppercase tracking-wide text-brand-dark">{{ $course['category'] }}</p>
                                <p class="mt-0.5 text-sm font-medium text-gray-900 leading-snug">{{ $course['title'] }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $course['lessons'] }} lessons · {{ $course['hours'] }}h</p>
                            </div>
                            <span class="text-sm font-medium text-gray-900 tabular-nums">${{ $course['price'] }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="checkout-order-promo">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Promo</span>
                            <span class="text-xs font-semibold text-gray-700">SKILLFORGE10</span>
                        </div>
                        <p class="mt-1 text-xs checkout-discount">− ${{ $discount }} applied</p>
                    </div>

                    <div class="checkout-order-lines">
                        <div class="checkout-order-line">
                            <span>Subtotal</span>
                            <span class="tabular-nums">${{ $subtotal }}</span>
                        </div>
                        <div class="checkout-order-line">
                            <span>Discount</span>
                            <span class="tabular-nums checkout-discount">− ${{ $discount }}</span>
                        </div>
                        <div class="checkout-order-line">
                            <span>Tax</span>
                            <span class="tabular-nums">$0</span>
                        </div>
                    </div>

                    <div class="checkout-order-total">
                        <span>Total</span>
                        <span class="tabular-nums">${{ $total }}</span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

@endsection
