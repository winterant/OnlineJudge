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

// 用户认证模块，包括登录注册等
Auth::routes();

// ================================ 主页 ================================
Route::get('/', 'Client\HomeController@home')->name('home');
Route::get('/home', 'Client\HomeController@home');


// ================================ 修改语言 ================================
Route::get('/change_language/{lang}', 'Client\UserController@change_language')->name('change_language');


// ================================ 提交记录 ================================
Route::get('/solutions', 'Client\SolutionController@solutions')->name('solutions');
Route::middleware(['auth', 'CheckUserLocked'])->where(['id' => '[0-9]+'])->group(function () {
    Route::get('/solutions/{id}', 'Client\SolutionController@solution')->name('solution');
    Route::get('/solutions/{id}/wrong_data/{type}', 'Client\SolutionController@solution_wrong_data')->name('solution_wrong_data')->where(['type' => '(in|out)']);
});


// ================================ 题目 ================================
Route::get('/problems', 'Client\ProblemController@problems')->name('problems');
Route::middleware(['CheckUserLocked'])->where(['id' => '[0-9]+'])->group(function () {
    Route::get('/problems/{id}', 'Client\ProblemController@problem')->name('problem');
});


// =============================== 题目 > 讨论板模块 ======================
// todo: 开发计划：取消讨论版功能，改为网站全局文章功能，每篇文章可以是题解、博文、知识讲解等。）
Route::post('/load_discussion', 'Client\ProblemController@load_discussion')->name('load_discussion');
Route::middleware(['auth', 'CheckUserLocked'])->group(function () {
    Route::post('/edit_discussion/{pid}', 'Client\ProblemController@edit_discussion')->name('edit_discussion')->where(['pid' => '[0-9]+']);
});
Route::middleware(['auth', 'CheckUserLocked', 'Permission:admin.problem.discussion'])->group(function () {
    Route::post('/delete_discussion', 'Client\ProblemController@delete_discussion')->name('delete_discussion');
    Route::post('/top_discussion', 'Client\ProblemController@top_discussion')->name('top_discussion');
    Route::post('/hidden_discussion', 'Client\ProblemController@hidden_discussion')->name('hidden_discussion');
});


// ================================ 竞赛 ================================
Route::middleware([])->where(['id' => '[0-9]+', 'bid' => '[0-9]+', 'nid' => '[0-9]+', 'bid' => '[0-9]+'])->group(function () {
    // 竞赛列表
    Route::get('contests', 'Client\ContestController@contests')->name('contests');
    // 竞赛详情
    Route::middleware(['auth', 'CheckContest', 'CheckUserLocked'])->group(function () {
        Route::get('contests/{id}', 'Client\ContestController@home')->name('contest.home');
        Route::get('contests/{id}/problems/{pid}', 'Client\ContestController@problem')->name('contest.problem');
        Route::get('contests/{id}/solutions', 'Client\ContestController@solutions')->name('contest.solutions');
        Route::get('contests/{id}/notices', 'Client\ContestController@notices')->name('contest.notices'); //公告
        // todo 获取公告 需要定制api
        Route::post('contests/{id}/get_notice', 'Client\ContestController@get_notice')->name('contest.get_notice'); //获取一条公告
        Route::get('contests/{id}/private_rank', 'Client\ContestController@rank')->name('contest.private_rank'); // 私有榜单
        // todo: 添加公告、删除公告 需要定制api
        Route::middleware(['Permission:admin.contest'])->group(function () {
            Route::post('contests/{id}/edit_notice', 'Client\ContestController@edit_notice')->name('contest.edit_notice'); //编辑/添加一条公告
            Route::post('contests/{id}/delete_notice/{nid}', 'Client\ContestController@delete_notice')->name('contest.delete_notice'); //删除一条公告
        });
        Route::middleware(['Permission:admin.contest.balloon'])->group(function () { //气球,需要权限
            Route::get('contests/{id}/balloons', 'Client\ContestController@balloons')->name('contest.balloons');
            // todo 派送气球 需要定制API
            Route::post('contests/{id}/deliver_ball/{bid}', 'Client\ContestController@deliver_ball')->name('contest.deliver_ball');
        });
    });
    Route::get('contests/{id}/rank', 'Client\ContestController@rank')->name('contest.rank'); // 公开榜单
    Route::any('contests/{id}/password', 'Client\ContestController@password')->middleware(['auth'])->name('contest.password');
});

// ================================ groups 群组/团队 ================================
Route::middleware(['auth'])->where(['id' => '[0-9]+', 'uid' => '[0-9]+'])->group(function () {
    // 群组列表
    Route::get('my-groups', 'Client\GroupController@mygroups')->name('groups.my');
    Route::get('groups', 'Client\GroupController@allgroups')->name('groups');
    // 具体的群组
    Route::middleware(['auth', 'CheckGroup', 'CheckUserLocked'])->group(function () {
        Route::get('groups/{id}', 'Client\GroupController@group')->name('group');
        Route::get('groups/{id}/members', 'Client\GroupController@members')->name('group.members');
        Route::get('groups/{id}/members/{uid}', 'Client\GroupController@member')->name('group.member');
    });
});

// ================================ 用户（users） ================================
Route::get('/standings', 'Client\UserController@standings')->name('standings');
Route::get('/users/{username}', 'Client\UserController@user')->name('user');
Route::middleware(['auth', 'CheckUserLocked'])->where(['id' => '[0-9]+'])->group(function () {
    Route::any('/users/{username}/edit', 'Client\UserController@user_edit')->name('user_edit');
    Route::any('/users/{username}/reset-password', 'Client\UserController@password_reset')->name('password_reset');
});


// ================================ Administration 后台管理 ================================
Route::middleware(['auth', 'CheckUserLocked'])->prefix('admin')->name('admin.')->where(['id' => '[0-9]+'])->group(function () {

    Route::middleware(['Permission:admin.home'])->group(function () {
        Route::get('/', 'Admin\HomeController@home')->name('home');
    });

    //    manage notice
    Route::middleware(['Permission:admin.notice'])->group(function () {
        Route::get('notices', 'Admin\NoticeController@list')->name('notice.list');
        Route::any('notices/{id}/update', 'Admin\NoticeController@update')->name('notice.update');
        Route::any('notice/add', 'Admin\NoticeController@add')->name('notice.add');
        // todo 删除、更新公告 需要定制api
        Route::post('notice/delete', 'Admin\NoticeController@delete')->name('notice.delete');
        Route::post('notice/update-state', 'Admin\NoticeController@update_state')->name('notice.update_state');
    });

    //   manage user
    Route::middleware(['Permission:admin.user'])->group(function () {
        // users
        Route::get('users', 'Admin\UserController@list')->name('user.list');
        Route::any('user/create', 'Admin\UserController@create')->name('user.create');
        Route::any('user/reset_pwd', 'Admin\UserController@reset_pwd')->name('user.reset_pwd');
        Route::post('user/delete', 'Admin\UserController@delete')->name('user.delete');
        Route::post('user/update-revise', 'Admin\UserController@update_revise')->name('user.update_revise');
        Route::post('user/update-locked', 'Admin\UserController@update_locked')->name('user.update_locked');
        // privileges
        Route::get('user/privileges', 'Admin\UserController@privileges')->name('user.privileges');
        // todo 添加、删除 特权 需要定制api（未来版本权限管理将改用spatie/laravel-permission）
        Route::post('user/privilege/create', 'Admin\UserController@privilege_create')->name('user.privilege_create');
        Route::post('user/privilege/delete', 'Admin\UserController@privilege_delete')->name('user.privilege_delete');
    });

    //   manage problem list
    Route::middleware(['Permission:admin.problem.list'])->group(function () {
        Route::get('problems', 'Admin\ProblemController@list')->name('problem.list');
    });

    //   manage problem editor
    Route::middleware(['Permission:admin.problem.edit'])->group(function () {
        Route::any('problem/add', 'Admin\ProblemController@add')->name('problem.add');
        Route::any('problems/{id}/update', 'Admin\ProblemController@update')->name('problem.update_withId');
        // todo 修改hidden需要定制api
        Route::post('problem/update-hidden', 'Admin\ProblemController@update_hidden')->name('problem.update_hidden');
        // todo 获取spj需要定制api
        Route::get('problems/{id}/get_spj', 'Admin\ProblemController@get_spj')->name('problem.get_spj');
    });

    //   manage problem tag
    Route::middleware(['Permission:admin.problem.tag'])->group(function () {
        Route::get('problem/tags', 'Admin\ProblemController@tags')->name('problem.tags');
        // todo 删除tag  需要定制api
        Route::post('problem/tags/delete', 'Admin\ProblemController@tag_delete')->name('problem.tag_delete');
        // tag_pool
        Route::get('problem/tag_pool', 'Admin\ProblemController@tag_pool')->name('problem.tag_pool');
        // todo 删除、修改  需要定制api
        Route::post('problem/tag_pool/delete', 'Admin\ProblemController@tag_pool_delete')->name('problem.tag_pool_delete');
        Route::post('problem/tag_pool/hidden', 'Admin\ProblemController@tag_pool_hidden')->name('problem.tag_pool_hidden');
    });

    //   manage problem data
    Route::middleware(['Permission:admin.problem.data'])->group(function () {
        Route::get('problem/test-data', 'Admin\ProblemController@test_data')->name('problem.test_data');
        Route::post('problem/upload-data', 'Admin\ProblemController@upload_data')->name('problem.upload_data');
        Route::post('problem/get-data', 'Admin\ProblemController@get_data')->name('problem.get_data');
        Route::post('problem/update-data', 'Admin\ProblemController@update_data')->name('problem.update_data');
        Route::post('problem/delete-data', 'Admin\ProblemController@delete_data')->name('problem.delete_data');
    });

    //   manage problem rejudge
    Route::middleware(['Permission:admin.problem.rejudge'])->group(function () {
        Route::any('problem/rejudge', 'Admin\ProblemController@rejudge')->name('problem.rejudge');
    });

    //   manage problem import export
    Route::middleware(['Permission:admin.problem.import_export'])->group(function () {
        Route::get('problem/import_export', 'Admin\ProblemController@import_export')->name('problem.import_export');
        Route::any('problem/import', 'Admin\ProblemController@import')->name('problem.import');
        Route::any('problem/export', 'Admin\ProblemController@export')->name('problem.export');
    });

    //   manage contest
    Route::middleware(['Permission:admin.contest'])->group(function () {
        Route::get('contests', 'Admin\ContestController@list')->name('contest.list');
        Route::any('contests/{id}/update', 'Admin\ContestController@update')->name('contest.update');
        // todo 删除附件需要定制api
        Route::post('contests/{id}/delete-file', 'Admin\ContestController@delete_file')->name('contest.delete_file');
        Route::any('contest/add', 'Admin\ContestController@add')->name('contest.add');
        // todo 以下3个需要定制api
        Route::post('contest/update-hidden', 'Admin\ContestController@update_hidden')->name('contest.update_hidden');
        Route::post('contest/update-public_rank', 'Admin\ContestController@update_public_rank')->name('contest.update_public_rank');
        Route::post('contest/clone', 'Admin\ContestController@clone')->name('contest.clone');
    });
    // 竞赛类别
    Route::middleware(['Permission:admin.contest.category'])->group(function () {
        Route::get('contest-categories', 'Admin\ContestController@categories')->name('contest.categories');
    });

    // manage group
    Route::middleware(['Permission:admin.group'])->group(function () {
        Route::get('groups', 'Admin\GroupController@list')->name('group.list');
        Route::get('groups/{id}/edit', 'Admin\GroupController@edit')->name('group.edit');
        Route::get('group/create', 'Admin\GroupController@create')->name('group.create');
    });

    // settings
    Route::middleware(['Permission:admin.setting'])->group(function () {
        Route::get('/settings', 'Admin\HomeController@settings')->name('settings');
    });
});
