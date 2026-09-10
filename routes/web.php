<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Instructor\AssessmentController as InstructorAssessmentController;
use App\Http\Controllers\Instructor\CourseBuilderController as InstructorCourseBuilderController;
use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboardController;
use App\Http\Controllers\Instructor\EnrolmentController as InstructorEnrolmentController;
use App\Http\Controllers\Instructor\ProfileController as InstructorProfileController;
use App\Http\Controllers\Instructor\ReportController as InstructorReportController;
use App\Http\Controllers\Instructor\SettingsController as InstructorSettingsController;
use App\Http\Controllers\Student\AssessmentController as StudentAssessmentController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\FeedbackController as StudentFeedbackController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\SettingsController as StudentSettingsController;
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
            'instructorSlug' => 'sarah-chen',
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
            'instructorSlug' => 'john-doe',
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
        $course['instructorSlug'] = 'sarah-chen';
    } else {
        $course = $courses[$slug];
    }

    return view('landing.course-show', ['course' => $course]);
})->name('courses.show');

Route::get('/instructors/{slug}', function (string $slug) {
    $instructors = config('instructors');

    if (! isset($instructors[$slug])) {
        abort(404);
    }

    return view('landing.instructor-show', ['instructor' => $instructors[$slug]]);
})->name('instructors.show');

Route::get('/about', function () {
    return view('landing.about');
})->name('about');

Route::get('/cart', function () {
    return view('landing.cart');
})->name('cart');

Route::get('/checkout', function () {
    return view('landing.checkout');
})->name('checkout');

Route::get('/wishlist', function () {
    return view('landing.wishlist');
})->name('wishlist');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::get('/register/student', [AuthController::class, 'showStudentRegister'])->name('register.student');
    Route::post('/register/student', [AuthController::class, 'registerStudent'])->name('register.student.submit');
    Route::get('/register/instructor', [AuthController::class, 'showInstructorRegister'])->name('register.instructor');
    Route::post('/register/instructor', [AuthController::class, 'registerInstructor'])->name('register.instructor.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route(auth()->user()->role->dashboardRoute());
    })->name('dashboard');

    Route::get('/dashboard/student', [StudentDashboardController::class, 'index'])
        ->middleware('role:student')
        ->name('dashboard.student.index');

    Route::middleware('role:student')->prefix('dashboard/student')->name('dashboard.student.')->group(function () {
        Route::get('/courses', [StudentCourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/browse', [StudentCourseController::class, 'browse'])->name('courses.browse');
        Route::post('/courses/{course}/enrol', [StudentCourseController::class, 'enrol'])->name('courses.enrol');
        Route::get('/courses/{course}', [StudentCourseController::class, 'show'])->name('courses.show');
        Route::patch('/courses/{course}/progress', [StudentCourseController::class, 'updateProgress'])->name('courses.progress');

        Route::get('/assessments', [StudentAssessmentController::class, 'index'])->name('assessments.index');
        Route::get('/assessments/{assessment}', [StudentAssessmentController::class, 'show'])->name('assessments.show');
        Route::post('/assessments/{assessment}/submit', [StudentAssessmentController::class, 'submit'])->name('assessments.submit');

        Route::get('/feedback', [StudentFeedbackController::class, 'index'])->name('feedback.index');
        Route::get('/feedback/{submission}', [StudentFeedbackController::class, 'show'])->name('feedback.show');

        Route::get('/profile', [StudentProfileController::class, 'show'])->name('profile.index');
        Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
        Route::get('/settings', [StudentSettingsController::class, 'show'])->name('settings.index');
        Route::put('/settings/password', [StudentSettingsController::class, 'updatePassword'])->name('settings.password');
    });

    Route::get('/dashboard/instructor', [InstructorDashboardController::class, 'index'])
        ->middleware('role:instructor')
        ->name('dashboard.instructor.index');

    Route::middleware('role:instructor')->prefix('dashboard/instructor')->name('dashboard.instructor.')->group(function () {
        Route::get('/courses', [InstructorCourseController::class, 'index'])->name('courses.index');
        Route::post('/courses', [InstructorCourseController::class, 'store'])->name('courses.store');
        Route::put('/courses/{course}', [InstructorCourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [InstructorCourseController::class, 'destroy'])->name('courses.destroy');
        Route::patch('/courses/{course}/toggle', [InstructorCourseController::class, 'toggleStatus'])->name('courses.toggle');

        Route::get('/builder', [InstructorCourseBuilderController::class, 'index'])->name('builder.index');
        Route::get('/builder/{course}', [InstructorCourseBuilderController::class, 'show'])->name('builder.show');
        Route::put('/builder/{course}/info', [InstructorCourseBuilderController::class, 'updateInfo'])->name('builder.info');
        Route::post('/builder/{course}/chapters', [InstructorCourseBuilderController::class, 'storeChapter'])->name('builder.chapters.store');
        Route::delete('/builder/{course}/chapters/{chapter}', [InstructorCourseBuilderController::class, 'destroyChapter'])->name('builder.chapters.destroy');
        Route::post('/builder/{course}/chapters/{chapter}/contents', [InstructorCourseBuilderController::class, 'storeContent'])->name('builder.contents.store');
        Route::delete('/builder/{course}/chapters/{chapter}/contents/{content}', [InstructorCourseBuilderController::class, 'destroyContent'])->name('builder.contents.destroy');

        Route::get('/assessments', [InstructorAssessmentController::class, 'index'])->name('assessments.index');
        Route::post('/assessments', [InstructorAssessmentController::class, 'store'])->name('assessments.store');
        Route::get('/assessments/{assessment}', [InstructorAssessmentController::class, 'show'])->name('assessments.show');
        Route::patch('/assessments/{assessment}/toggle', [InstructorAssessmentController::class, 'toggleStatus'])->name('assessments.toggle');
        Route::delete('/assessments/{assessment}', [InstructorAssessmentController::class, 'destroy'])->name('assessments.destroy');
        Route::post('/assessments/{assessment}/questions', [InstructorAssessmentController::class, 'storeQuestion'])->name('assessments.questions.store');
        Route::delete('/assessments/{assessment}/questions/{question}', [InstructorAssessmentController::class, 'destroyQuestion'])->name('assessments.questions.destroy');
        Route::put('/submissions/{submission}/grade', [InstructorAssessmentController::class, 'gradeSubmission'])->name('submissions.grade');

        Route::get('/enrolments', [InstructorEnrolmentController::class, 'index'])->name('enrolments.index');
        Route::get('/reports', [InstructorReportController::class, 'index'])->name('reports.index');

        Route::get('/profile', [InstructorProfileController::class, 'show'])->name('profile.index');
        Route::put('/profile', [InstructorProfileController::class, 'update'])->name('profile.update');
        Route::get('/settings', [InstructorSettingsController::class, 'show'])->name('settings.index');
        Route::put('/settings/password', [InstructorSettingsController::class, 'updatePassword'])->name('settings.password');
    });

    Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])
        ->middleware('role:admin')
        ->name('dashboard.admin.index');

    Route::middleware('role:admin')->prefix('dashboard/admin')->name('dashboard.admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/toggle', [AdminUserController::class, 'toggleStatus'])->name('users.toggle');

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/courses', [AdminCourseController::class, 'index'])->name('courses.index');

        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');

        Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile.index');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

        Route::get('/settings', [AdminSettingsController::class, 'show'])->name('settings.index');
        Route::put('/settings/password', [AdminSettingsController::class, 'updatePassword'])->name('settings.password');
    });
});
