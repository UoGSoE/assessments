<?php

Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/home', function () {
    // "somewhere" in the login/redirect code it *sometimes* redirects to /home
    // so this is here to catch that. Funz... :-/
    return redirect('/');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', 'HomeController@index')->name('home');

    Route::get('/assessment/{id}', 'AssessmentController@show')->name('assessment.show');
    Route::post('/assessment/{id}/feedback', 'StudentFeedbackController@store')->name('feedback.store');
    Route::post('/assessment/{id}/feedback_complete', 'StaffFeedbackController@store')->name('feedback.complete');

    Route::get('course/{id}', 'CourseController@show')->name('course.show');

    Route::group(['middleware' => 'admin', 'prefix' => 'admin'], function () {

        Route::get('report/feedback', 'ReportController@feedback')->name('report.feedback');
        Route::get('report/assessment', 'ReportController@assessments')->name('report.assessment');
        Route::get('report/staff', 'ReportController@staff')->name('report.staff');

        Route::get('student/{id}', 'StudentController@show')->name('student.show');
        Route::get('staff/{id}', 'StaffController@show')->name('staff.show');
        Route::post('staff/{id}/admin', 'StaffController@toggleAdmin')->name('staff.toggle_admin');

        Route::get('/assessent/create', 'AssessmentController@create')->name('assessment.create');
        Route::post('/assessment', 'AssessmentController@store')->name('assessment.store');
        Route::get('/assessment/{id}/edit', 'AssessmentController@edit')->name('assessment.edit');
        Route::post('/assessment/{id}', 'AssessmentController@update')->name('assessment.update');
        Route::delete('/assessment/{id}', 'AssessmentController@destroy')->name('assessment.destroy');

        Route::get('/coursework', 'CourseworkController@edit')->name('coursework.edit');
        Route::post('/coursework', 'CourseworkController@update')->name('coursework.update');

        Route::get('/export/assessments', 'ExportController@assessments')->name('export.assessments');
        Route::get('/export/staff', 'ExportController@staff')->name('export.staff');

        Route::delete('/clear', 'OldDataController@destroy')->name('admin.clearold');
    });
});
