<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Public\CartController;
use App\Http\Controllers\Public\CatalogController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\WishlistController;
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

// ---------------------------------------------------------------------------
// Public pages
// ---------------------------------------------------------------------------
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/courses', [HomeController::class, 'courses'])->name('courses');
Route::get('/courses/{course:slug}', [CatalogController::class, 'show'])->name('courses.show');
Route::get('/courses/{course:slug}/preview/{content}', [CatalogController::class, 'freePreview'])->name('courses.lesson-preview');
Route::get('/instructors/{user}', [CatalogController::class, 'instructor'])->name('instructors.show');

Route::get('/about', function () {
    return view('landing.about');
})->name('about');

// Cart & wishlist (session based so guests can collect courses before logging in)
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/{course:slug}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/{course:slug}/buy', [CartController::class, 'buyNow'])->name('cart.buy');
Route::delete('/cart/{course:slug}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
Route::post('/wishlist/{course:slug}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::post('/wishlist/{course:slug}/cart', [WishlistController::class, 'moveToCart'])->name('wishlist.cart');
Route::post('/wishlist-all/cart', [WishlistController::class, 'moveAllToCart'])->name('wishlist.cart.all');

// Checkout (students only)
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('/checkout/receipt/{reference}', [CheckoutController::class, 'receipt'])->name('checkout.receipt');
});
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
        Route::get('/courses/{course}', [StudentCourseController::class, 'show'])->name('courses.show');
        Route::get('/courses/{course}/lessons/{content}', [StudentCourseController::class, 'lesson'])->name('courses.lesson');
        Route::post('/courses/{course}/lessons/{content}/complete', [StudentCourseController::class, 'completeLesson'])->name('courses.lesson.complete');

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
        Route::patch('/courses/{course}/archive', [InstructorCourseController::class, 'archive'])->name('courses.archive');

        Route::get('/builder', [InstructorCourseBuilderController::class, 'index'])->name('builder.index');
        Route::get('/builder/{course}', [InstructorCourseBuilderController::class, 'show'])->name('builder.show');
        Route::put('/builder/{course}/info', [InstructorCourseBuilderController::class, 'updateInfo'])->name('builder.info');
        Route::post('/builder/{course}/chapters', [InstructorCourseBuilderController::class, 'storeChapter'])->name('builder.chapters.store');
        Route::delete('/builder/{course}/chapters/{chapter}', [InstructorCourseBuilderController::class, 'destroyChapter'])->name('builder.chapters.destroy');
        Route::post('/builder/{course}/chapters/{chapter}/contents', [InstructorCourseBuilderController::class, 'storeContent'])->name('builder.contents.store');
        Route::delete('/builder/{course}/chapters/{chapter}/contents/{content}', [InstructorCourseBuilderController::class, 'destroyContent'])->name('builder.contents.destroy');
        Route::get('/builder/{course}/preview', [CatalogController::class, 'preview'])->name('builder.preview');
        Route::put('/builder/{course}/chapters/{chapter}', [InstructorCourseBuilderController::class, 'updateChapter'])->name('builder.chapters.update');
        Route::post('/builder/{course}/chapters/{chapter}/move', [InstructorCourseBuilderController::class, 'moveChapter'])->name('builder.chapters.move');
        Route::get('/builder/{course}/chapters/{chapter}/contents/{content}/edit', [InstructorCourseBuilderController::class, 'editContent'])->name('builder.contents.edit');
        Route::put('/builder/{course}/chapters/{chapter}/contents/{content}', [InstructorCourseBuilderController::class, 'updateContent'])->name('builder.contents.update');
        Route::post('/builder/{course}/chapters/{chapter}/contents/{content}/move', [InstructorCourseBuilderController::class, 'moveContent'])->name('builder.contents.move');
        Route::post('/builder/{course}/chapters/{chapter}/assessments', [InstructorCourseBuilderController::class, 'storeAssessment'])->name('builder.assessments.store');

        Route::get('/assessments', [InstructorAssessmentController::class, 'index'])->name('assessments.index');
        Route::post('/assessments', [InstructorAssessmentController::class, 'store'])->name('assessments.store');
        Route::get('/assessments/{assessment}', [InstructorAssessmentController::class, 'show'])->name('assessments.show');
        Route::patch('/assessments/{assessment}/toggle', [InstructorAssessmentController::class, 'toggleStatus'])->name('assessments.toggle');
        Route::delete('/assessments/{assessment}', [InstructorAssessmentController::class, 'destroy'])->name('assessments.destroy');
        Route::post('/assessments/{assessment}/questions', [InstructorAssessmentController::class, 'storeQuestion'])->name('assessments.questions.store');
        Route::delete('/assessments/{assessment}/questions/{question}', [InstructorAssessmentController::class, 'destroyQuestion'])->name('assessments.questions.destroy');
        Route::put('/submissions/{submission}/grade', [InstructorAssessmentController::class, 'gradeSubmission'])->name('submissions.grade');
        Route::post('/submissions/{submission}/rescore', [InstructorAssessmentController::class, 'rescoreSubmission'])->name('submissions.rescore');
        Route::put('/assessments/{assessment}', [InstructorAssessmentController::class, 'updateSettings'])->name('assessments.update');

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
