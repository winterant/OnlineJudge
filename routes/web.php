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
Route::get('/solution/{id}','Client\StatusController@solution')->where(['id'=>'[0-9]+'])->name('solution');
Route::get('/problems','Client\ProblemController@problems')->name('problems');
Route::get('/problem/{id}','Client\ProblemController@problem')->where(['id'=>'[0-9]+'])->name('problem');
Route::get('/contests','Client\ContestController@contests')->name('contests');
Route::get('/user/{username}','Client\UserController@user')->name('user');
Route::any('/user/{username}/edit','Client\UserController@user_edit')->middleware('auth')->name('user_edit');
Route::any('/user/{username}/password_reset','Client\UserController@password_reset')
    ->middleware('auth')->name('password_reset');

Route::post('/status/submit_solution','Client\StatusController@create')->middleware('auth')->name('submit_solution');


// Contest
Route::middleware(['auth','CheckContest'])->prefix('contest/{id}')
    ->name('contest.')->where(['id'=>'[0-9]+'])->group(function () {

    Route::get('/', 'Client\ContestController@home')->name('home');

    Route::get('/problem/{pid}', 'Client\ContestController@problem')
        ->where(['pid'=>'[0-9]+'])->name('problem');
    Route::get('/status', 'Client\ContestController@status')->name('status');
    Route::get('/rank', 'Client\ContestController@rank')->name('rank');
    Route::post('/cancel_lock', 'Client\ContestController@cancel_lock')->name('cancel_lock');//取消封榜

    Route::get('/statistics', 'Client\ContestController@statistics')->name('statistics');
});
Route::any('contest/{id}/password', 'Client\ContestController@password')
    ->middleware(['auth'])->name('contest.password'); //不检查密码


// Administration
Route::middleware(['auth','CheckAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', 'Admin\HomeController@index')->name('home');

//    user
    Route::get('/users', 'Admin\UserController@users')->name('users');
    Route::post('/change_revise_to','Admin\UserController@change_revise_to')->name('change_revise_to');


//    problem
    Route::get('/problems', 'Admin\ProblemController@problems')->name('problems');
    Route::any('/add_problem','Admin\ProblemController@add_problem')->name('add_problem');
    Route::get('/update_problem','Admin\ProblemController@update_problem')->name('update_problem');
    Route::any('/update_problem/{id}','Admin\ProblemController@update_problem')->name('update_problem_withId');
    Route::post('/change_state_to','Admin\ProblemController@change_state_to')->name('change_state_to');
    Route::any('/rejudge','Admin\ProblemController@rejudge')->name('rejudge');
});
