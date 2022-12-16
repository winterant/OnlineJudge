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

Route::namespace('Api')->name('api.')->where(['id' => '[0-9]+', 'uid' => '[0-9]+'])->group(function () {
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
        Route::get('/solutions/{id}', 'SolutionController@solution_result')->name('solution.solution_result');
        Route::post('/solution/test', 'SolutionController@submit_local_test')->name('solution.submit_local_test');
    });


    // =====================================================================
    // ============================ admin ==================================
    Route::prefix('admin')->name('admin.')->middleware(['auth:api', 'CheckUserLocked'])->group(function () {
        // Manage solution: route('api.admin.solution.*')
        Route::middleware(['Permission:admin'])->group(function () {
            // Route::post('/solution/correct-submitted-count', 'Admin\SolutionController@correct_submitted_count')->name('solution.correct_submitted_count');
        });

        // Manage user: route('api.admin.user.*')
        Route::middleware(['Permission:admin.user'])->group(function () {
            Route::put('/user/roles', 'Admin\UserController@create_role')->name('user.create_role');
            Route::patch('/user/roles/{id}', 'Admin\UserController@update_role')->name('user.update_role');
            Route::delete('/user/roles/{id}', 'Admin\UserController@delete_role')->name('user.delete_role');
            Route::get('/user/roles/{id}/permissions', 'Admin\UserController@get_role_permissions')->name('user.get_role_permissions');
            Route::put('/user/roles/{id}/users/batch', 'Admin\UserController@role_add_users')->name('user.role_add_users');
            Route::delete('/user/roles/{id}/users/{uid}', 'Admin\UserController@role_delete_user')->name('user.role_delete_user');
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
            // contests
            Route::post('/groups/{id}/contests', 'Admin\GroupController@create_contests')->name('group.create_contests');
            Route::delete('/groups/{id}/contests/batch', 'Admin\GroupController@delete_contests_batch')->name('group.delete_contests_batch');
            Route::patch('/group-contests/{id}/order/{shift}', 'Admin\GroupController@update_contest_order')->name('group.update_contest_order')->where(['shift' => '^(\-|\+)?[0-9]+']);
            // members
            Route::post('/groups/{id}/members', 'Admin\GroupController@create_members')->name('group.create_members');
            Route::delete('/groups/{id}/members/batch', 'Admin\GroupController@delete_members_batch')->name('group.delete_members_batch');
            Route::patch('/group/members/batch', 'Admin\GroupController@update_members_batch')->name('group.update_members_batch');
        });
        // settings
        Route::middleware(['Permission:admin.setting'])->group(function () {
            Route::patch('/settings', 'Admin\HomeController@settings')->name('settings');
        });
    });
});
