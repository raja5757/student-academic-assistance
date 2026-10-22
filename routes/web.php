<?php

use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\StudentAcademicController;
use App\Http\Controllers\StudyPlanController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Guest Routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    // Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendForgotPasswordOtp'])->name('password.email');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // Google Authentication
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Email Verification Routes
    Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/verify-email/resend', [AuthController::class, 'resendVerificationEmail'])->name('verification.send');

    // Verified Routes
    Route::middleware('verified')->group(function () {
        // Dashboard redirect based on role
        Route::get('/dashboard', [DashboardController::class, 'redirect'])->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Student Routes
    |----------------------------------------------------------------------
    */
    Route::middleware('role:student')->prefix('student')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'student'])->name('student.dashboard');

        // Academics
        Route::get('/academics', [StudentAcademicController::class, 'index'])->name('student.academics');
        Route::post('/assignments', [StudentAcademicController::class, 'storeAssignment'])->name('student.assignments.store');
        Route::patch('/assignments/{assignment}', [StudentAcademicController::class, 'updateAssignment'])->name('student.assignments.update');
        Route::post('/exams', [StudentAcademicController::class, 'storeExam'])->name('student.exams.store');
        Route::post('/attendance', [StudentAcademicController::class, 'storeAttendance'])->name('student.attendance.store');
        Route::post('/marks', [StudentAcademicController::class, 'storeMark'])->name('student.marks.store');
        Route::post('/documents', [StudentAcademicController::class, 'uploadDocument'])->name('student.documents.upload');

        // AI Assistant
        Route::get('/ai-assistant', [AiAssistantController::class, 'index'])->name('ai.assistant');
        Route::post('/ai-assistant/ask', [AiAssistantController::class, 'ask'])->name('ai.assistant.ask');

        // Study Plans
        Route::get('/study-plans', [StudyPlanController::class, 'index'])->name('study-plans.index');
        Route::post('/study-plans/generate', [StudyPlanController::class, 'generate'])->name('study-plans.generate');
        Route::post('/study-plans/adapt', [StudyPlanController::class, 'adapt'])->name('study-plans.adapt');
        Route::patch('/study-tasks/{task}/toggle', [StudyPlanController::class, 'toggleTask'])->name('study-tasks.toggle');
        Route::patch('/study-tasks/{task}', [StudyPlanController::class, 'updateTask'])->name('study-tasks.update');
        Route::delete('/study-tasks/{task}', [StudyPlanController::class, 'destroyTask'])->name('study-tasks.destroy');

        // Quizzes
        Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
        Route::post('/quizzes/generate', [QuizController::class, 'generate'])->name('quizzes.generate');
        Route::get('/quizzes/{quiz}/attempt', [QuizController::class, 'attempt'])->name('quizzes.attempt');
        Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit'])->name('quizzes.submit');
        Route::get('/quizzes/result/{attempt}', [QuizController::class, 'result'])->name('quizzes.result');

        // Profile
        Route::get('/profile', [StudentAcademicController::class, 'profile'])->name('student.profile');
        Route::patch('/profile', [StudentAcademicController::class, 'updateProfile'])->name('student.profile.update');
    });

    /*
    |----------------------------------------------------------------------
    | Teacher Routes
    |----------------------------------------------------------------------
    */
    Route::middleware('role:teacher')->prefix('teacher')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'teacher'])->name('teacher.dashboard');
        Route::get('/courses', [TeacherController::class, 'courses'])->name('teacher.courses');
        Route::get('/courses/{course}', [TeacherController::class, 'courseDetail'])->name('teacher.courses.show');
        Route::get('/students/{student}', [TeacherController::class, 'studentDetail'])->name('teacher.students.show');
        Route::post('/attendance', [TeacherController::class, 'storeAttendance'])->name('teacher.attendance.store');
        Route::post('/marks', [TeacherController::class, 'storeMarks'])->name('teacher.marks.store');
        Route::post('/assignments', [TeacherController::class, 'storeAssignment'])->name('teacher.assignments.store');
        Route::post('/exams', [TeacherController::class, 'storeExam'])->name('teacher.exams.store');
        Route::get('/profile', [TeacherController::class, 'profile'])->name('teacher.profile');
        Route::patch('/profile', [TeacherController::class, 'updateProfile'])->name('teacher.profile.update');
    });

    /*
    |----------------------------------------------------------------------
    | Admin Routes
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::patch('/users/{user}/status', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle');
        Route::get('/departments', [AdminController::class, 'departments'])->name('admin.departments');
        Route::post('/departments', [AdminController::class, 'storeDepartment'])->name('admin.departments.store');
        Route::get('/subjects', [AdminController::class, 'subjects'])->name('admin.subjects');
        Route::post('/subjects', [AdminController::class, 'storeSubject'])->name('admin.subjects.store');
        Route::get('/students', [AdminController::class, 'students'])->name('admin.students');
        Route::get('/students/{student}', [AdminController::class, 'studentDetail'])->name('admin.students.show');
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('admin.activity-logs');
    });
    });
});
