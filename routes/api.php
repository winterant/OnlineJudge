<?php

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

Route::namespace('Api')->name('api.')->where(['id' => '[0-9]+'])->group(function () {
    // ========================= CK editor upload image API =========================
    /**
     * Usage Example
     *   Backend URL: route('api.ckeditor_files')
     *   Frontend URI: /api/ckeditor-files
     *   Response: {
     *     uploaded: boolean,
     *     url: string
     *   }
     */
    Route::post('/ckeditor-files', 'UploadController@ckeditor_files')->name('ckeditor_files');

    // =========================== Online Judge API =================================
    /**
     * Usage Example:
     *   Backend URL: route('api.solution.submit_solution')
     *   Frontend URI: /api/solutions
     *   Response: {
     *     ok: (0|1),
     *     msg: string,
     *     data: json
     *   }
     */

    // =========================== notice ===================================
    Route::name('notice.')->group(function () {
        Route::get('/notices/{id}', 'NoticeController@get_notice')->name('get_notice');
    });

    // =========================== problem ===================================
    Route::name('problem.')->group(function () {
        Route::post('/problem-tags', 'ProblemController@submit_problem_tag')->name('submit_problem_tag');
    });

    // =========================== solution =================================
    Route::name('solution.')->group(function () {
        Route::middleware(['auth:api', 'CheckUserLocked'])->group(function () {
            Route::post('/solutions', 'SolutionController@submit_solution')->name('submit_solution');
            Route::post('/solutions/test', 'SolutionController@submit_local_test')->name('submit_local_test');
            Route::get('/solutions/{id}', 'SolutionController@solution_result')->name('solution_result');
        });
    });

    // ============================ admin ==================================
    Route::prefix('admin')->name('admin.')->middleware(['auth:api', 'CheckUserLocked'])->group(function () {
        // Manage solution: route('api.admin.solution.*')
        Route::name('solution.')->group(function () {
            Route::middleware(['Permission:admin'])->group(function () {
                Route::post('/solutions/statistics', 'Admin\SolutionController@correct_submitted_count')->name('correct_submitted_count');
            });
        });

        // Manage contest: route('api.admin.contest.*')
        Route::name('contest.')->middleware(['Permission:admin.contest'])->group(function () {
            Route::patch('/contests/{id}/order/{shift}', 'Admin\ContestController@update_order')->name('update_order')->where(['shift' => '^(\-|\+)?[0-9]+']);
            Route::patch('/contests/{id}/cate_id/{cate_id}', 'Admin\ContestController@update_cate_id')->name('update_cate_id');
        });

        // Manage contest category: route('api.admin.contest.*')
        Route::name('contest.')->middleware(['Permission:admin.contest.category'])->group(function () {
            Route::post('/contest-categaries', 'Admin\ContestController@add_contest_cate')->name('add_contest_cate');
            Route::put('/contest-categaries/{id}', 'Admin\ContestController@update_contest_cate')->name('update_contest_cate');
            Route::delete('/contest-categaries/{id}', 'Admin\ContestController@delete_contest_cate')->name('delete_contest_cate');
            Route::patch('/contest-categaries/{id}/order/{shift}', 'Admin\ContestController@update_contest_cate_order')->name('update_contest_cate_order')->where(['shift' => '^(\-|\+)?[0-9]+']);
        });
    });
});
