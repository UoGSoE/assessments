<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', 'StudentHomeController@index')->name('home');
    Route::get('/assessment/{id}', 'AssessmentController@show')->name('assessment.show');
    Route::post('/assessment/{id}/feedback', 'StudentFeedbackController@store')->name('feedback.store');

    Route::group(['middleware' => 'admin', 'prefix' => 'admin'], function () {
        Route::get('report', 'ReportController@assessments')->name('report.assessments');
        Route::get('student/{id}', 'StudentController@show')->name('student.show');
    });
});
