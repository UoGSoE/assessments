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

Route::get('/', 'StudentHomeController@index')->name('home');
Route::post('/logout', function () {
})->name('logout');
Route::post('/assessment/{id}/feedback', 'StudentFeedbackController@store')->name('feedback.store');
