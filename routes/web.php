<?php

// public routes

Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/assessment/{id}', [\App\Http\Controllers\AssessmentController::class, 'show'])->name('assessment.show');
Route::get('/course/{id}', [\App\Http\Controllers\CourseController::class, 'show'])->name('course.show');

// authenticated routes

Route::middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\HomeController::class, 'landing'])->name('landing');

    Route::get('/home', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/student/{id}', [\App\Http\Controllers\StudentController::class, 'show'])->name('student.show');

    Route::post('/assessment/{id}/feedback', [\App\Http\Controllers\StudentFeedbackController::class, 'store'])->name('feedback.store');
    Route::post('/assessment/{id}/feedback_complete', [\App\Http\Controllers\StaffFeedbackController::class, 'store'])->name('feedback.complete');

    // admin only routes

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('report/feedback', [\App\Http\Controllers\ReportController::class, 'feedback'])->name('report.feedback');
        Route::get('report/assessment', [\App\Http\Controllers\ReportController::class, 'assessments'])->name('report.assessment');
        Route::get('report/staff', [\App\Http\Controllers\ReportController::class, 'staff'])->name('report.staff');

        Route::get('staff/{id}', [\App\Http\Controllers\StaffController::class, 'show'])->name('staff.show');
        Route::post('staff/{id}/admin', [\App\Http\Controllers\StaffController::class, 'toggleAdmin'])->name('staff.toggle_admin');

        Route::get('/assessent/create', [\App\Http\Controllers\AssessmentController::class, 'create'])->name('assessment.create');
        Route::post('/assessment', [\App\Http\Controllers\AssessmentController::class, 'store'])->name('assessment.store');
        Route::get('/assessment/{id}/edit', [\App\Http\Controllers\AssessmentController::class, 'edit'])->name('assessment.edit');
        Route::post('/assessment/{id}', [\App\Http\Controllers\AssessmentController::class, 'update'])->name('assessment.update');
        Route::delete('/assessment/{id}', [\App\Http\Controllers\AssessmentController::class, 'destroy'])->name('assessment.destroy');

        Route::get('/coursework', [\App\Http\Controllers\CourseworkController::class, 'edit'])->name('coursework.edit');
        Route::post('/coursework', [\App\Http\Controllers\CourseworkController::class, 'update'])->name('coursework.update');

        Route::get('/export/assessments', [\App\Http\Controllers\ExportController::class, 'assessments'])->name('export.assessments');
        Route::get('/export/staff', [\App\Http\Controllers\ExportController::class, 'staff'])->name('export.staff');

        Route::delete('/clear', [\App\Http\Controllers\OldDataController::class, 'destroy'])->name('admin.clearold');
    });
});

// API routes

Route::get('/api/assessments', [\App\Http\Controllers\ApiController::class, 'assessmentsAsJson']);
