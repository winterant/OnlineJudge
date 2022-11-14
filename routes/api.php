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

Route::namespace('Api')->name('api.')->where(['id' => '[0-9]+','uid' => '[0-9]+'])->group(function () {
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
    Route::middleware([])->group(function () {
        Route::get('/notices/{id}', 'NoticeController@get_notice')->name('notice.get_notice');
    });

    // =========================== problem ===================================
    Route::middleware([])->group(function () {
        Route::post('/problem-tags', 'ProblemController@submit_problem_tag')->name('problem.submit_problem_tag');
    });

    // =========================== solution =================================
    Route::middleware(['auth:api', 'CheckUserLocked'])->group(function () {
        Route::post('/solutions', 'SolutionController@submit_solution')->name('solution.submit_solution');
        Route::post('/solutions/test', 'SolutionController@submit_local_test')->name('solution.submit_local_test');
        Route::get('/solutions/{id}', 'SolutionController@solution_result')->name('solution.solution_result');
    });


    // =====================================================================
    // ============================ admin ==================================
    Route::prefix('admin')->name('admin.')->middleware(['auth:api', 'CheckUserLocked'])->group(function () {
        // Manage solution: route('api.admin.solution.*')
        Route::middleware(['Permission:admin'])->group(function () {
            Route::post('/solutions/statistics', 'Admin\SolutionController@correct_submitted_count')->name('solution.correct_submitted_count');
        });

        // Manage contest: route('api.admin.contest.*')
        Route::middleware(['Permission:admin.contest'])->group(function () {
            Route::patch('/contests/{id}/order/{shift}', 'Admin\ContestController@update_order')->name('contest.update_order')->where(['shift' => '^(\-|\+)?[0-9]+']);
            Route::patch('/contests/{id}/cate_id/{cate_id}', 'Admin\ContestController@update_cate_id')->name('contest.update_cate_id');
        });

        // Manage contest category: route('api.admin.contest.*')
        Route::middleware(['Permission:admin.contest.category'])->group(function () {
            Route::post('/contest-categaries', 'Admin\ContestController@add_contest_cate')->name('contest.add_contest_cate');
            Route::put('/contest-categaries/{id}', 'Admin\ContestController@update_contest_cate')->name('contest.update_contest_cate');
            Route::delete('/contest-categaries/{id}', 'Admin\ContestController@delete_contest_cate')->name('contest.delete_contest_cate');
            Route::patch('/contest-categaries/{id}/order/{shift}', 'Admin\ContestController@update_contest_cate_order')->name('contest.update_contest_cate_order')->where(['shift' => '^(\-|\+)?[0-9]+']);
        });

        // Manage group: route('api.admin.group.*')
        Route::middleware(['Permission:admin.group'])->group(function () {
            Route::post('/groups', 'Admin\GroupController@create')->name('group.create');
            Route::put('/groups/{id}', 'Admin\GroupController@update')->name('group.update');
            Route::delete('/groups/{id}', 'Admin\GroupController@delete')->name('group.delete');
            Route::patch('/groups/batch', 'Admin\GroupController@update_batch')->name('group.update_batch');
            // members
            Route::post('/groups/{id}/members', 'Admin\GroupController@create_members')->name('group.create_members');
            Route::delete('/groups/{id}/members/batch', 'Admin\GroupController@delete_members_batch')->name('group.delete_members_batch');
            Route::patch('/group/members/batch', 'Admin\GroupController@update_members_batch')->name('group.update_members_batch');
        });
    });
});
