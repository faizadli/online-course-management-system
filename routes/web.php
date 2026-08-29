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
