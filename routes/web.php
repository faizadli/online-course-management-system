<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('landing.index');
})->name('home');

Route::get('/courses', function () {
    return view('landing.courses');
})->name('courses');

Route::get('/courses/{slug}', function (string $slug) {
    $courses = [
        'ui-ux-design-fundamentals' => [
            'slug' => 'ui-ux-design-fundamentals',
            'title' => 'UI/UX Design Fundamentals',
            'category' => 'Design & Creative',
            'instructor' => 'Sarah Chen',
            'instructorRole' => 'Senior Product Designer',
            'instructorBio' => 'Sarah has 10+ years of experience designing products for startups and Fortune 500 companies. She specializes in user research, wireframing, and design systems.',
            'avatar' => 'photo-1494790108377-be9c29b29330',
            'image' => 'photo-1561070791-2526d30994b5',
            'price' => 49,
            'originalPrice' => 99,
            'rating' => '4.9',
            'students' => '3,240',
            'lessons' => 24,
            'hours' => 18,
            'badge' => 'Best Seller',
            'badgeColor' => 'bg-red-500',
            'description' => 'Master the core principles of UI/UX design — from user research and wireframing to prototyping and usability testing. Build a portfolio-ready project by the end of this course.',
            'learn' => [
                'Conduct user research and create personas',
                'Design wireframes and interactive prototypes',
                'Apply visual design principles and typography',
                'Build a complete design system in Figma',
                'Run usability tests and iterate on feedback',
                'Present your work to stakeholders confidently',
            ],
            'curriculum' => [
                ['title' => 'Introduction to UI/UX', 'lessons' => 4, 'duration' => '2h 30m', 'items' => ['What is UI/UX design?', 'Design thinking process', 'Tools overview', 'Setting up Figma']],
                ['title' => 'User Research', 'lessons' => 5, 'duration' => '3h 15m', 'items' => ['Research methods', 'Creating personas', 'User journey mapping', 'Competitive analysis', 'Research presentation']],
                ['title' => 'Wireframing & Prototyping', 'lessons' => 6, 'duration' => '4h', 'items' => ['Low-fidelity wireframes', 'Information architecture', 'Interactive prototypes', 'Micro-interactions', 'Prototype testing', 'Iteration workflow']],
                ['title' => 'Visual Design & Portfolio', 'lessons' => 9, 'duration' => '8h 15m', 'items' => ['Color theory', 'Typography systems', 'Design systems', 'Final project brief', 'Portfolio presentation']],
            ],
        ],
        'full-stack-web-development' => [
            'slug' => 'full-stack-web-development',
            'title' => 'Full Stack Web Development',
            'category' => 'Tech & Programming',
            'instructor' => 'John Doe',
            'instructorRole' => 'Lead Software Engineer',
            'instructorBio' => 'John is a full-stack engineer with experience building scalable web applications. He has taught over 15,000 students worldwide.',
            'avatar' => 'photo-1507003211169-0a1dd7228f2d',
            'image' => 'photo-1498050108023-c5249f4df085',
            'price' => 79,
            'originalPrice' => 149,
            'rating' => '4.8',
            'students' => '5,120',
            'lessons' => 36,
            'hours' => 42,
            'badge' => 'New',
            'badgeColor' => 'bg-brand',
            'description' => 'Learn to build modern web applications from scratch using HTML, CSS, JavaScript, and Laravel. Deploy a full-stack project to production by the end of the course.',
            'learn' => [
                'Build responsive layouts with HTML & CSS',
                'Write clean JavaScript and manage state',
                'Create RESTful APIs with Laravel',
                'Connect frontend to backend seamlessly',
                'Implement authentication and authorization',
                'Deploy your app to a live server',
            ],
            'curriculum' => [
                ['title' => 'Frontend Foundations', 'lessons' => 8, 'duration' => '6h', 'items' => ['HTML semantics', 'CSS Flexbox & Grid', 'Responsive design', 'JavaScript basics']],
                ['title' => 'Backend with Laravel', 'lessons' => 12, 'duration' => '14h', 'items' => ['Laravel setup', 'Routing & controllers', 'Eloquent ORM', 'API development']],
                ['title' => 'Full Stack Project', 'lessons' => 16, 'duration' => '22h', 'items' => ['Project planning', 'Building features', 'Testing', 'Deployment']],
            ],
        ],
    ];

    if (! isset($courses[$slug])) {
        $course = $courses['ui-ux-design-fundamentals'];
        $course['slug'] = $slug;
        $course['title'] = Str::headline(str_replace('-', ' ', $slug));
        $course['badge'] = null;
        $course['badgeColor'] = '';
    } else {
        $course = $courses[$slug];
    }

    return view('landing.course-show', ['course' => $course]);
})->name('courses.show');

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
