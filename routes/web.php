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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::middleware([])->where(['id' => '[0-9]+', 'bid' => '[0-9]+', 'nid' => '[0-9]+', 'uid' => '[0-9]+'])->group(function () {
    // ================================ 用户认证路由 ================================
    Auth::routes();


    // ================================ 主页 ================================
    Route::get('/', 'HomeController@home')->name('home');
    Route::get('/home', 'HomeController@home');


    // ================================ 修改语言 ================================
    Route::get('/change_language/{lang}', 'UserController@change_language')->name('change_language');


    // ================================ 提交记录 ================================
    Route::get('/solutions', 'SolutionController@solutions')->name('solutions');
    Route::get('/solutions/{id}', 'SolutionController@solution')->name('solution');
    Route::get('/solutions/{id}/wrong_data/{type}', 'SolutionController@solution_wrong_data')->name('solution_wrong_data')->where(['type' => '(in|out)']);


    // ================================ 题目 ================================
    Route::get('/problems', 'ProblemController@problems')->name('problems');
    Route::get('/problems/{id}', 'ProblemController@problem')->name('problem');


    // =============================== 题目 > 讨论板模块 ======================
    // 已废弃
    // todo: 开发计划：取消讨论版功能，改为网站全局文章功能，每篇文章可以是题解、博文、知识讲解等。）
    Route::post('/load_discussion', 'ProblemController@load_discussion')->name('load_discussion');
    Route::middleware(['auth'])->group(function () {
        Route::post('/edit_discussion/{pid}', 'ProblemController@edit_discussion')->name('edit_discussion');
    });
    // 已废弃的功能
    Route::middleware(['auth', 'Permission:admin.problem'])->group(function () {
        Route::post('/delete_discussion', 'ProblemController@delete_discussion')->name('delete_discussion');
        Route::post('/top_discussion', 'ProblemController@top_discussion')->name('top_discussion');
        Route::post('/hidden_discussion', 'ProblemController@hidden_discussion')->name('hidden_discussion');
    });


    // ================================ 竞赛 ================================
    // 竞赛列表
    Route::get('contests', 'ContestController@contests')->name('contests');
    Route::get('contests/{id}/rank', 'ContestController@rank')->name('contest.rank'); // 公开榜单
    Route::any('contests/{id}/password', 'ContestController@password')->middleware(['auth'])->name('contest.password');
    // 竞赛详情
    Route::middleware(['auth', 'CheckContest'])->group(function () {
        Route::get('contests/{id}', 'ContestController@home')->name('contest.home');
        Route::get('contests/{id}/problems/{index}', 'ContestController@problem')->name('contest.problem');
        Route::get('contests/{id}/solutions', 'ContestController@solutions')->name('contest.solutions');
        Route::get('contests/{id}/notices', 'ContestController@notices')->name('contest.notices'); //公告
        Route::get('contests/{id}/private_rank', 'ContestController@rank')->name('contest.private_rank'); // 私有榜单
        Route::middleware('Permission:admin.contest_balloon')->group(function () {
            Route::get('contests/{id}/balloons', 'ContestController@balloons')->name('contest.balloons');
            Route::post('contests/{id}/deliver_ball/{bid}', 'ContestController@deliver_ball')->name('contest.deliver_ball');
        });
    });


    // ================================ groups 群组/团队 ================================
    // 群组列表
    Route::get('groups', 'GroupController@groups')->name('groups');
    // 具体的群组
    Route::middleware(['auth', 'CheckGroup'])->group(function () {
        Route::get('groups/{id}', 'GroupController@group')->name('group');
        Route::get('groups/{id}/solutions', 'GroupController@solutions')->name('group.solutions');
        Route::get('groups/{id}/members', 'GroupController@members')->name('group.members');
        Route::get('groups/{id}/members/{username}', 'GroupController@member')->name('group.member');
    });

    // ================================ 用户（users） ================================
    Route::get('/standings', 'UserController@standings')->name('standings');
    Route::get('/users/{username}', 'UserController@user')->name('user');
    Route::any('/users/{username}/edit', 'UserController@edit')->name('user.edit')->middleware('Permission:admin.user.update,users.{username}.id');
    Route::any('/users/{username}/reset-password', 'UserController@password_reset')->name('password_reset')->middleware('Permission:admin.user.update,users.{username}.id');


    // ================================ Administration 后台管理 ================================
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        // ===================== Admin Home
        Route::get('/', 'Admin\HomeController@home')->name('home')->middleware('Permission:admin.view');

        // ===================== Manage notice
        Route::get('notices', 'Admin\NoticeController@list')->name('notice.list')->middleware('Permission:admin.notice.view');
        Route::any('notice/add', 'Admin\NoticeController@add')->name('notice.add')->middleware('Permission:admin.notice.create');
        Route::any('notices/{id}/update', 'Admin\NoticeController@update')->name('notice.update')->middleware('Permission:admin.notice.update,notices.{id}.user_id');
        // todo 删除、更新公告 需要定制api
        Route::post('notice/delete', 'Admin\NoticeController@delete')->name('notice.delete')->middleware('Permission:admin.notice.delete');
        Route::post('notice/update-state', 'Admin\NoticeController@update_state')->name('notice.update_state')->middleware('Permission:admin.notice.update');

        // ===================== Manage user
        Route::get('users', 'Admin\UserController@list')->name('user.list')->middleware('Permission:admin.user.view');
        Route::get('user/create', 'Admin\UserController@create')->name('user.create')->middleware('Permission:admin.user.create');
        Route::get('user/reset_password', 'Admin\UserController@reset_password')->name('user.reset_password')->middleware('Permission:admin.user.update');
        Route::post('user/update-revise', 'Admin\UserController@update_revise')->name('user.update_revise')->middleware('Permission:admin.user.update');
        Route::post('user/update-locked', 'Admin\UserController@update_locked')->name('user.update_locked')->middleware('Permission:admin.user.update');

        // ====================== 用户角色管理 （权限内置不可修改）spatie/laravel-permission. https://spatie.be/docs/laravel-permission/v5/basic-usage/basic-usage
        Route::get('user/roles', 'Admin\UserController@roles')->name('user.roles')->middleware('Permission:admin.user_role.view');

        // ====================== Manage problem list
        Route::get('problems', 'Admin\ProblemController@list')->name('problem.list')->middleware('Permission:admin.problem.view');

        // ====================== Manage problem editor
        Route::any('problem/add', 'Admin\ProblemController@add')->name('problem.add')->middleware('Permission:admin.problem.create');
        Route::any('problems/{id}/update', 'Admin\ProblemController@update')->name('problem.update_withId')->middleware('Permission:admin.problem.update,problems.{id}.user_id');
        // todo 修改hidden需要定制api
        Route::post('problem/update-hidden', 'Admin\ProblemController@update_hidden')->name('problem.update_hidden')->middleware('Permission:admin.problem.update');

        // ====================== Manage problem tag
        Route::get('problem/tags', 'Admin\ProblemController@tags')->name('problem.tags')->middleware('Permission:admin.problem_tag.view');
        // tag_pool
        Route::get('problem/tag_pool', 'Admin\ProblemController@tag_pool')->name('problem.tag_pool')->middleware('Permission:admin.problem_tag.view');

        // ====================== Manage problem data
        Route::get('problem/test-data', 'Admin\ProblemController@test_data')->name('problem.test_data')->middleware('Permission:admin.problem_data.view');
        Route::post('problem/upload-data', 'Admin\ProblemController@upload_data')->name('problem.upload_data')->middleware('Permission:admin.problem_data.create');
        Route::post('problem/get-data', 'Admin\ProblemController@get_data')->name('problem.get_data')->middleware('Permission:admin.problem_data.view');
        Route::post('problem/update-data', 'Admin\ProblemController@update_data')->name('problem.update_data')->middleware('Permission:admin.problem_data.update');
        Route::post('problem/delete-data', 'Admin\ProblemController@delete_data')->name('problem.delete_data')->middleware('Permission:admin.problem_data.delete');

        // ====================== Manage problem import export
        Route::get('problem/import_export', 'Admin\ProblemController@import_export')->name('problem.import_export')->middleware('Permission:admin.problem_xml.view');
        Route::post('problem/import', 'Admin\ProblemController@import')->name('problem.import')->middleware('Permission:admin.problem_xml.import');
        Route::post('problem/export', 'Admin\ProblemController@export')->name('problem.export')->middleware('Permission:admin.problem_xml.export');

        // ====================== Manage solution rejudge
        Route::any('solution/rejudge', 'Admin\SolutionController@rejudge')->name('solution.rejudge')->middleware('Permission:admin.solution.rejudge');

        // ====================== Manage contest
        Route::get('contests', 'Admin\ContestController@list')->name('contest.list')->middleware('Permission:admin.contest.view');
        Route::any('contests/{id}/update', 'Admin\ContestController@update')->name('contest.update')->middleware('Permission:admin.contest.view,contests.{id}.user_id');
        // todo 删除附件需要定制api
        Route::post('contests/{id}/delete-file', 'Admin\ContestController@delete_file')->name('contest.delete_file')->middleware('Permission:admin.contest.update');
        Route::any('contest/add', 'Admin\ContestController@add')->name('contest.add')->middleware('Permission:admin.contest.create');
        // todo 以下3个需要定制api
        Route::post('contest/update-hidden', 'Admin\ContestController@update_hidden')->name('contest.update_hidden')->middleware('Permission:admin.contest.update');
        Route::post('contest/update-public_rank', 'Admin\ContestController@update_public_rank')->name('contest.update_public_rank')->middleware('Permission:admin.contest.update');
        Route::post('contest/clone', 'Admin\ContestController@clone')->name('contest.clone')->middleware('Permission:admin.contest.create');

        // ===================== 竞赛类别
        Route::get('contest-categories', 'Admin\ContestController@categories')->name('contest.categories')->middleware('Permission:admin.contest_cate.view');

        // ===================== Manage group
        Route::get('groups', 'Admin\GroupController@list')->name('group.list')->middleware('Permission:admin.group.view');
        Route::get('group/create', 'Admin\GroupController@create')->name('group.create')->middleware('Permission:admin.group.create');
        Route::get('groups/{id}/edit', 'Admin\GroupController@edit')->name('group.edit')->middleware('Permission:admin.group.update,groups.{id}.user_id');

        // ===================== settings
        Route::get('/settings', 'Admin\HomeController@settings')->name('settings')->middleware('Permission:admin.setting.view');
    });
});
