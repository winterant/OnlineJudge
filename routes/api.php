<?php

use App\Http\Controllers\Api\SolutionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
reponse return {
    'ok':1,
    'msg':'',
    'data':{}
}
*/

Route::namespace('Api')->name('api.')->group(function () {
    // =========================== Solution =================================
    Route::name('solution.')->where(['id' => '[0-9]+'])->group(function () {
        Route::middleware(['auth:api', 'CheckUserLocked'])->group(function () {
            Route::post('/solutions', 'SolutionController@submit_solution')->name('submit_solution');
            Route::post('/solutions/test', 'SolutionController@submit_local_test')->name('submit_local_test');
            Route::get('/solutions/{id}', 'SolutionController@solution_result')->name('solution_result');
        });
    });

    // CK editor upload image
    Route::post('/ck_upload_image', 'UploadController@ck_upload_image')->name('ck_upload_image');

    // ========================= 管理员；auth:api要求api_token正确 =========================
    Route::middleware(['auth:api', 'CheckUserLocked'])->prefix('admin')->name('admin.')->where(['id' => '[0-9]+'])->group(function () {
        //   manage contest
        Route::middleware(['Permission:admin.contest'])->prefix('contest')->name('contest.')->group(function () {
            // Route::post('/delete', 'Admin\ContestController@delete')->name('delete');
            // Route::post('/delete/file/{id}', 'Admin\ContestController@delete_file')->name('delete_file');
            // Route::post('/update/hidden', 'Admin\ContestController@update_hidden')->name('update_hidden');
            // Route::post('/update/public_rank', 'Admin\ContestController@update_public_rank')->name('update_public_rank');
            Route::post('/update_contest_order/{id}/{mode}', 'Admin\ContestController@update_contest_order')->name('update_contest_order');
            Route::post('/update_contest_cate_id/{id}/{cate_id}', 'Admin\ContestController@update_contest_cate_id')->name('update_contest_cate_id');

            Route::post('/cancel_lock', 'Client\ContestController@cancel_lock')->name('cancel_lock'); //取消封榜
        });
        // contest category
        Route::middleware(['Permission:admin.contest.category'])->prefix('contest')->name('contest.')->group(function () {
            Route::post('/add_contest_cate', 'Admin\ContestController@add_contest_cate')->name('add_contest_cate');
            Route::post('/update_contest_cate/{id}', 'Admin\ContestController@update_contest_cate')->name('update_contest_cate');
            Route::post('/delete_contest_cate/{id}', 'Admin\ContestController@delete_contest_cate')->name('delete_contest_cate');
            Route::post('/update_cate_order/{id}/{mode}', 'Admin\ContestController@update_cate_order')->name('update_cate_order');
        });
    });
});
