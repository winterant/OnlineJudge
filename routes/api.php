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

Route::namespace('Api')->name('api.')->where(['id' => '[0-9]+', 'uid' => '[0-9]+', 'shift' => '^(\-|\+)?[0-9]+'])->group(function () {
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

    // =========================== contest ===================================
    Route::middleware([])->group(function () {
        Route::get('contests/{id}/notices/{nid}', 'ContestController@get_notice')->name('contest.get_notice'); //获取一条公告
    });

    // =========================== solution =================================
    Route::middleware(['auth'])->group(function () {
        Route::post('/solutions', 'SolutionController@submit_solution')->name('solution.submit_solution');
        Route::get('/solutions/{id}', 'SolutionController@solution_result')->name('solution.solution_result')->middleware('Permission:admin.solution.view,solutions.{id}.user_id');
        Route::post('/solution/test', 'SolutionController@submit_local_test')->name('solution.submit_local_test');
    });


    // =====================================================================
    // ============================ admin ==================================
    Route::middleware(['auth'])->group(function () {
        // Manage user: route('api.admin.user.*')
        Route::post('admin/user/create/batch', 'UserController@create_batch')->name('admin.user.create_batch')->middleware('Permission:admin.user.create');
        Route::get('admin/user/create/download', 'UserController@download_created_users_csv')->name('admin.user.download_created_users_csv')->middleware('Permission:admin.user.create');
        Route::post('admin/user/delete/batch', 'UserController@delete_batch')->name('admin.user.delete_batch')->middleware('Permission:admin.user.delete');
        Route::patch('admin/user/reset_password', 'UserController@reset_password')->name('admin.user.reset_password')->middleware('Permission:admin.user.update');

        // permission and role
        Route::post('admin/user/roles', 'UserController@create_role')->name('admin.user.create_role')->middleware('Permission:admin.user_role.create');
        Route::patch('admin/user/roles/{id}', 'UserController@update_role')->name('admin.user.update_role')->middleware('Permission:admin.user_role.update');
        Route::delete('admin/user/roles/{id}', 'UserController@delete_role')->name('admin.user.delete_role')->middleware('Permission:admin.user_role.delete');
        Route::get('admin/user/roles/{id}/permissions', 'UserController@get_role_permissions')->name('admin.user.get_role_permissions')->middleware('Permission:admin.user_role.view');
        Route::post('admin/user/roles/{id}/users/batch', 'UserController@role_add_users')->name('admin.user.role_add_users')->middleware('Permission:admin.user_role.update');
        Route::delete('admin/user/roles/{id}/users/{uid}', 'UserController@role_delete_user')->name('admin.user.role_delete_user')->middleware('Permission:admin.user_role.delete');

        // Manage problem: route('api.admin.problem.*')
        Route::delete('admin/problems/{id}', 'ProblemController@delete')->name('admin.problem.delete')->middleware('Permission:admin.problem.delete');// 创建人无法删除
        Route::get('admin/problem/export/download', 'ProblemController@download_exported_xml')->name('admin.problem.download_exported_xml')->middleware('Permission:admin.problem_xml.export');
        Route::delete('admin/problem/export/clear', 'ProblemController@clear_exported_xml')->name('admin.problem.clear_exported_xml')->middleware('Permission:admin.problem_xml');

        // Manage tag and tag_pool
        Route::delete('problem/tags/batch', 'ProblemController@tag_delete_batch')->name('admin.problem.tag_delete_batch')->middleware('Permission:admin.problem_tag.delete');
        Route::patch('problem/tag_pool/{id}', 'ProblemController@tag_pool_update')->name('admin.problem.tag_pool_update')->middleware('Permission:admin.problem_tag.update');
        Route::patch('problem/tag_pool/batch', 'ProblemController@tag_pool_update_batch')->name('admin.problem.tag_pool_update_batch')->middleware('Permission:admin.problem_tag.update');
        Route::delete('problem/tag_pool/batch', 'ProblemController@tag_pool_delete_batch')->name('admin.problem.tag_pool_delete_batch')->middleware('Permission:admin.problem_tag.delete');

        // Manage contest: route('api.admin.contest.*')
        Route::delete('admin/contests/{id}', 'ContestController@delete')->name('admin.contest.delete')->middleware('Permission:admin.contest.delete');
        Route::patch('admin/contests/{id}/order/{shift}', 'ContestController@update_order')->name('admin.contest.update_order')->middleware('Permission:admin.contest.update');
        Route::patch('admin/contests/{id}/cate_id/{cate_id}', 'ContestController@update_cate_id')->name('admin.contest.update_cate_id')->middleware('Permission:admin.contest.update');

        // Manage contest notice: route('api.admin.contest.*')
        Route::post('admin/contests/{id}/notices', 'ContestController@create_notice')->name('admin.contest.create_notice')->middleware('Permission:admin.contest_notice.create'); //添加一条公告
        Route::patch('admin/contests/{id}/notices/{nid}', 'ContestController@update_notice')->name('admin.contest.update_notice')->middleware('Permission:admin.contest_notice.update'); //编辑一条公告
        Route::delete('admin/contests/{id}/notices/{nid}', 'ContestController@delete_notice')->name('admin.contest.delete_notice')->middleware('Permission:admin.contest_notice.delete'); //删除一条公告

        // Manage contest category: route('api.admin.contest.*')
        Route::post('admin/contest-categaries', 'ContestController@add_contest_cate')->name('admin.contest.add_contest_cate')->middleware('Permission:admin.contest_cate.create');
        Route::patch('admin/contest-categaries/{id}', 'ContestController@update_contest_cate')->name('admin.contest.update_contest_cate')->middleware('Permission:admin.contest_cate.update');
        Route::delete('admin/contest-categaries/{id}', 'ContestController@delete_contest_cate')->name('admin.contest.delete_contest_cate')->middleware('Permission:admin.contest_cate.delete');
        Route::patch('admin/contest-categaries/{id}/order/{shift}', 'ContestController@update_contest_cate_order')->name('admin.contest.update_contest_cate_order')->middleware('Permission:admin.contest_cate.update');

        // Manage group: route('api.admin.group.*')
        Route::post('admin/groups', 'GroupController@create')->name('admin.group.create')->middleware('Permission:admin.group.create');
        Route::delete('admin/groups/{id}', 'GroupController@delete')->name('admin.group.delete')->middleware('Permission:admin.group.delete');
        Route::put('admin/groups/{id}', 'GroupController@update')->name('admin.group.update')->middleware('Permission:admin.group.update,groups.{id}.user_id');
        Route::patch('admin/groups/batch-to-one', 'GroupController@update_batch_to_one')->name('admin.group.update_batch_to_one')->middleware('Permission:admin.group.update');

        // 对group的竞赛、成员的管理，控制器中控制权限
        // contests
        Route::post('admin/groups/{id}/contests', 'GroupController@create_contests')->name('admin.group.create_contests')->middleware('Permission:admin.group.update,groups.{id}.user_id');
        Route::delete('admin/groups/{id}/contests/batch', 'GroupController@delete_contests_batch')->name('admin.group.delete_contests_batch')->middleware('Permission:admin.group.delete,groups.{id}.user_id');
        Route::patch('admin/groups/{id}/group-contests/{gcid}/order/{shift}', 'GroupController@update_contest_order')->name('admin.group.update_contest_order')->middleware('Permission:admin.group.update,groups.{id}.user_id');
        // members
        Route::post('admin/groups/{id}/members', 'GroupController@create_members')->name('admin.group.create_members')->middleware('Permission:admin.group.update,groups.{id}.user_id');
        Route::delete('admin/groups/{id}/members/batch', 'GroupController@delete_members_batch')->name('admin.group.delete_members_batch')->middleware('Permission:admin.group.delete,groups.{id}.user_id');
        Route::patch('admin/groups/{id}/members/batch-to-one', 'GroupController@update_members_batch_to_one')->name('admin.group.update_members_batch_to_one')->middleware('Permission:admin.group.update,groups.{id}.user_id');
        // 群组成员个人档案
        Route::get('admin/groups/{id}/members/{username}/archive', 'GroupController@get_archive')->name('admin.group.get_archive')->middleware('Permission:admin.group.view,groups.{id}.user_id');
        Route::get('admin/groups/{id}/members/{username}/archive-history', 'GroupController@get_archive_history')->name('admin.group.get_archive_history')->middleware('Permission:admin.group.view,groups.{id}.user_id');
        Route::patch('admin/groups/{id}/members/{username}', 'GroupController@update_archive')->name('admin.group.update_archive')->middleware('Permission:admin.group.update,groups.{id}.user_id');

        // settings
        Route::patch('admin/settings', 'SettingController@settings')->name('admin.settings')->middleware('Permission:admin.setting.update');
    });
});
