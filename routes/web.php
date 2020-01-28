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


// Authorization
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Auth::routes();

// Client
Route::get('/', 'Client\HomeController@index')->name('home');
Route::get('/home', 'Client\HomeController@index');
Route::get('/status','Client\StatusController@index')->name('status');
Route::get('/problems','Client\ProblemController@problems')->name('problems');
Route::get('/problem/{id}','Client\ProblemController@problem')->where(['id'=>'[0-9]+'])->name('problem');

Route::post('/status/submit_solution','Client\StatusController@create')->middleware('auth')->name('submit_solution');


// Administration
Route::middleware(['auth','CheckAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', 'Admin\HomeController@index')->name('home');

    Route::get('/users', 'Admin\UserController@users')->name('users');

    Route::get('/problems', 'Admin\ProblemController@problems')->name('problems');
    Route::any('/add_problem','Admin\ProblemController@add_problem')->name('add_problem');
    Route::get('/update_problem','Admin\ProblemController@update_problem')->name('update_problem');
    Route::any('/update_problem/{id}','Admin\ProblemController@update_problem')->name('update_problem_withId');
    Route::post('/change_state_to','Admin\ProblemController@change_state_to')->name('change_state_to');
});
