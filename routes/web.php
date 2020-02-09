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
Route::post('/get_notice','Client\HomeController@get_notice')->name('get_notice');
Route::get('/status','Client\StatusController@index')->name('status');
Route::get('/solution/{id}','Client\StatusController@solution')->where(['id'=>'[0-9]+'])->name('solution');
Route::get('/problems','Client\ProblemController@problems')->name('problems');
Route::get('/problem/{id}','Client\ProblemController@problem')->where(['id'=>'[0-9]+'])->name('problem');
Route::get('/contests','Client\ContestController@contests')->name('contests');
Route::get('/user/{username}','Client\UserController@user')->name('user');
Route::middleware(['auth'])->group(function (){
    Route::any('/user/{username}/edit','Client\UserController@user_edit')->name('user_edit');
    Route::any('/user/{username}/password_reset','Client\UserController@password_reset')->name('password_reset');
    Route::post('/status/submit_solution','Client\StatusController@create')->name('submit_solution');
});



// Contest
Route::prefix('contest/{id}')->name('contest.')->where(['id'=>'[0-9]+'])->where(['pid'=>'[0-9]+'])->group(function () {

    Route::middleware(['auth','CheckContest'])->group(function (){
        Route::get('/', 'Client\ContestController@home')->name('home');
        Route::get('/problem/{pid}', 'Client\ContestController@problem')->name('problem');
        Route::get('/status', 'Client\ContestController@status')->name('status');
        Route::post('/cancel_lock', 'Client\ContestController@cancel_lock')->name('cancel_lock');//取消封榜
    });
    Route::any('/password', 'Client\ContestController@password')->middleware(['auth'])->name('password');
    Route::get('/rank', 'Client\ContestController@rank')->name('rank');
//    Route::get('/statistics', 'Client\ContestController@statistics')->name('statistics');
});


// Administration
Route::middleware(['auth'])->prefix('admin')->name('admin.')->where(['id'=>'[0-9]+'])->group(function () {
    Route::get('/', 'Admin\HomeController@index')->name('home');

//    manage notice
    Route::middleware(['Privilege:admin'])->prefix('notice')->name('notice.')->group(function (){
        Route::get('/list','Admin\NoticeController@list')->name('list');
        Route::any('/add','Admin\NoticeController@add')->name('add');
        Route::any('/update/{id}','Admin\NoticeController@update')->name('update');
    });

//   manage user
    Route::middleware(['Privilege:admin'])->prefix('user')->name('user.')->group(function (){
        Route::get('/list', 'Admin\UserController@list')->name('list');
        Route::get('/privileges', 'Admin\UserController@privileges')->name('privileges');
        Route::any('/create','Admin\UserController@create')->name('create');
        Route::post('/revise/change','Admin\UserController@change_revise')->name('revise.change');
        Route::post('/privilege/change','Admin\UserController@change_privilege')->name('privilege.change');
    });

//   manage problem
    Route::middleware(['Privilege:problem'])->prefix('problem')->name('problem.')->group(function (){
        Route::get('/list', 'Admin\ProblemController@list')->name('list');
        Route::any('/add','Admin\ProblemController@add')->name('add');
        Route::get('/update','Admin\ProblemController@update')->name('update');
        Route::any('/update/{id}','Admin\ProblemController@update')->name('update_withId');
        Route::post('/hidden/change','Admin\ProblemController@change_hidden')->name('hidden.change');
        Route::any('/rejudge','Admin\ProblemController@rejudge')->name('rejudge');
    });

//   manage contest
    Route::middleware(['Privilege:contest'])->prefix('contest')->name('contest.')->group(function (){
        Route::get('/list','Admin\ContestController@list')->name('list');
        Route::get('/add','Admin\ContestController@add')->name('add');
        Route::get('/update','Admin\ContestController@update')->name('update');
        Route::get('/update/{id}','Admin\ContestController@update')->name('update_withId');
        Route::post('/delete','Admin\ContestController@delete')->name('delete');
        Route::post('/hidden/change','Admin\ContestController@change_hidden')->name('hidden.change');
    });
});
