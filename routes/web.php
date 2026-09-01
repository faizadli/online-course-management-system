<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing.index');
})->name('home');

Route::get('/courses', function () {
    return view('landing.courses');
})->name('courses');

Route::get('/about', function () {
    return view('landing.about');
})->name('about');

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

Route::get('/cart', function () {
    return view('landing.cart');
})->name('cart');

Route::get('/checkout', function () {
    return view('landing.checkout');
})->name('checkout');

Route::get('/login', function () {
    return view('landing.login');
})->name('login');

Route::get('/wishlist', function () {
    return view('landing.wishlist');
})->name('wishlist');
