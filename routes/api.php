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
    // Usage Example:
    //   Backend:  route('api.solution.submit_solution')
    //   Frontend: 'http://<domain>/api/solutions'
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
    // Usage Example:
    //   Backend:  route('api.admin.contest.update_order')
    //   Frontend: 'http://<domain>/api/contests/1/order/-1'
    Route::middleware(['auth:api', 'CheckUserLocked'])->prefix('admin')->name('admin.')->where(['id' => '[0-9]+'])->group(function () {
        // Manage contest
        Route::middleware(['Permission:admin.contest'])->name('contest.')->group(function () {

            Route::patch('/contests/{id}/order/{shift}', 'Admin\ContestController@update_order')->name('update_order')->where(['shift' => '^(\-|\+)?[0-9]+']);
            Route::patch('/contests/{id}/cate_id/{cate_id}', 'Admin\ContestController@update_cate_id')->name('update_cate_id');
        });

        // Manage contest category
        Route::middleware(['Permission:admin.contest.category'])->name('contest.')->group(function () {
            Route::post('/contest-categaries', 'Admin\ContestController@add_contest_cate')->name('add_contest_cate');
            Route::patch('/contest-categaries/{id}', 'Admin\ContestController@update_contest_cate')->name('update_contest_cate');
            Route::delete('/contest-categaries/{id}', 'Admin\ContestController@delete_contest_cate')->name('delete_contest_cate');
            Route::patch('/contest-categaries/{id}/order/{shift}', 'Admin\ContestController@update_cate_order')->name('update_cate_order')->where(['shift' => '^(\-|\+)?[0-9]+']);
        });
    });
});
