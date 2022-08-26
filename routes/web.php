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

// 用户认证模块，包括登录注册等
Auth::routes();

// Client 用户前台各个页面
Route::get('/', 'Client\HomeController@index')->name('home');
Route::get('/home', 'Client\HomeController@index');
Route::post('/get_notice', 'Client\HomeController@get_notice')->name('get_notice');
Route::get('/status', 'Client\StatusController@index')->name('status');
Route::post('/ajax_get_status', 'Client\StatusController@ajax_get_status')->name('ajax_get_status');
Route::get('/problems', 'Client\ProblemController@problems')->name('problems');
Route::get('/problem/{id}', 'Client\ProblemController@problem')->middleware('CheckBlacklist')->where(['id' => '[0-9]+'])->name('problem');
Route::get('/contests', 'Client\ContestController@contests')->name('contests');
Route::get('/contests/{cate}', 'Client\ContestController@contests')->name('contests');

Route::get('/standings', 'Client\UserController@standings')->name('standings');
Route::get('/user/{username}', 'Client\UserController@user')->name('user');
Route::get('/change_language/{lang}', 'Client\UserController@change_language')->name('change_language');
Route::middleware(['auth', 'CheckBlacklist'])->where(['id' => '[0-9]+'])->group(function () {
    Route::get('/solution/{id}', 'Client\StatusController@solution')->name('solution');
    Route::get('/solution/{id}/wrong_data/{type}', 'Client\StatusController@solution_wrong_data')
        ->where(['type' => '(in|out)'])->name('solution_wrong_data');
    Route::any('/user/{username}/edit', 'Client\UserController@user_edit')->name('user_edit');
    Route::any('/user/{username}/password_reset', 'Client\UserController@password_reset')->name('password_reset');
    Route::post('/status/submit_solution', 'Client\StatusController@submit_solution')->name('submit_solution');
    //    CK editor upload image
    Route::post('/ck_upload_image', 'UploadController@ck_upload_image')->name('ck_upload_image');

    //tag marking
    Route::post('/tag_mark', 'Client\ProblemController@tag_mark')->name('tag_mark');
});

//  题目页面讨论板模块
Route::post('/load_discussion', 'Client\ProblemController@load_discussion')->name('load_discussion');
Route::middleware(['auth', 'CheckBlacklist'])->group(function () {
    Route::post('/edit_discussion/{pid}', 'Client\ProblemController@edit_discussion')->name('edit_discussion');
});
Route::middleware(['auth', 'CheckBlacklist', 'Privilege:admin.problem.discussion'])->group(function () {
    Route::post('/delete_discussion', 'Client\ProblemController@delete_discussion')->name('delete_discussion');
    Route::post('/top_discussion', 'Client\ProblemController@top_discussion')->name('top_discussion');
    Route::post('/hidden_discussion', 'Client\ProblemController@hidden_discussion')->name('hidden_discussion');
});


// Contest，用户前台竞赛页面所有路由
Route::prefix('contest/{id}')->name('contest.')->where(['id' => '[0-9]+'])->where(['pid' => '[0-9]+'])->group(function () {

    Route::middleware(['auth', 'CheckContest', 'CheckBlacklist'])->group(function () {
        Route::get('/', 'Client\ContestController@home')->name('home');
        Route::get('/problem/{pid}', 'Client\ContestController@problem')->name('problem');
        Route::get('/status', 'Client\ContestController@status')->name('status');
        Route::get('/notices', 'Client\ContestController@notices')->name('notices'); //公告
        Route::post('/get_notice', 'Client\ContestController@get_notice')->name('get_notice'); //获取一条公告
        Route::get('/private_rank', 'Client\ContestController@rank')->name('private_rank'); // 私有榜单

        Route::middleware(['Privilege:admin.contest'])->group(function () {
            Route::post('/cancel_lock', 'Client\ContestController@cancel_lock')->name('cancel_lock'); //取消封榜
            Route::post('/edit_notice', 'Client\ContestController@edit_notice')->name('edit_notice'); //编辑/添加一条公告
            Route::post('/delete_notice/{nid}', 'Client\ContestController@delete_notice')->name('delete_notice'); //删除一条公告
            Route::post('/start_to_judge', 'Client\ContestController@start_to_judge')->name('start_to_judge');
        });

        Route::middleware(['Privilege:admin.contest.balloon'])->group(function () { //气球,需要权限
            Route::get('/balloons', 'Client\ContestController@balloons')->name('balloons');
            Route::post('/deliver_ball/{bid}', 'Client\ContestController@deliver_ball')->name('deliver_ball');
        });
    });

    Route::any('/password', 'Client\ContestController@password')->middleware(['auth'])->name('password');
    Route::get('/rank', 'Client\ContestController@rank')->name('rank');
});

// group，用户前台group页面所有路由
Route::middleware(['auth'])->group(function () {
    Route::get('/groups', 'Client\GroupController@groups')->name('groups');
    Route::get('/groups/my', 'Client\GroupController@mygroups')->name('groups.my');
    Route::get('/groups/all', 'Client\GroupController@allgroups')->name('groups.all');
    Route::get('/groups/joinin/{id}', 'Client\GroupController@joinin')->name('groups.joinin');
});
Route::prefix('group/{id}')->name('group.')->where(['id' => '[0-9]+'])->where(['pid' => '[0-9]+'])->group(function () {
    Route::middleware(['auth', 'CheckGroup', 'CheckBlacklist'])->group(function () {
        Route::get('/', 'Client\GroupController@home')->name('home');
        Route::get('/members', 'Client\GroupController@members')->name('members');
    });
});


// Administration 管理员后台页面所有路由
Route::middleware(['auth', 'CheckBlacklist'])->prefix('admin')->name('admin.')->where(['id' => '[0-9]+'])->group(function () {

    Route::middleware(['Privilege:admin.home'])->group(function () {
        Route::get('/', 'Admin\HomeController@index')->name('home');
    });

    //    manage notice
    Route::middleware(['Privilege:admin.notice'])->prefix('notice')->name('notice.')->group(function () {
        Route::get('/list', 'Admin\NoticeController@list')->name('list');
        Route::any('/add', 'Admin\NoticeController@add')->name('add');
        Route::any('/update/{id}', 'Admin\NoticeController@update')->name('update');
        Route::post('/delete', 'Admin\NoticeController@delete')->name('delete');
        Route::post('/update/state', 'Admin\NoticeController@update_state')->name('update_state');
    });

    //   manage user
    Route::middleware(['Privilege:admin.user'])->prefix('user')->name('user.')->group(function () {
        Route::get('/list', 'Admin\UserController@list')->name('list');
        Route::get('/privileges', 'Admin\UserController@privileges')->name('privileges');
        Route::any('/create', 'Admin\UserController@create')->name('create');
        Route::post('/delete', 'Admin\UserController@delete')->name('delete');
        Route::post('/update/revise', 'Admin\UserController@update_revise')->name('update_revise');
        Route::post('/update/locked', 'Admin\UserController@update_locked')->name('update_locked');
        Route::post('/privilege/create', 'Admin\UserController@privilege_create')->name('privilege_create');
        Route::post('/privilege/delete', 'Admin\UserController@privilege_delete')->name('privilege_delete');
        Route::any('/reset_pwd', 'Admin\UserController@reset_pwd')->name('reset_pwd');
        Route::get('/blacklist', 'Admin\UserController@blacklist')->name('blacklist');
    });

    //   manage problem list
    Route::middleware(['Privilege:admin.problem.list'])->prefix('problem')->name('problem.')->group(function () {
        Route::get('/list', 'Admin\ProblemController@list')->name('list');
    });

    //   manage problem editor
    Route::middleware(['Privilege:admin.problem.edit'])->prefix('problem')->name('problem.')->group(function () {
        Route::any('/add', 'Admin\ProblemController@add')->name('add');
        // Route::get('/update', 'Admin\ProblemController@update')->name('update');
        Route::any('/update/{id}', 'Admin\ProblemController@update')->name('update_withId');
        Route::post('/update/hidden', 'Admin\ProblemController@update_hidden')->name('update_hidden');
        Route::get('/get_spj/{pid}', 'Admin\ProblemController@get_spj')->name('get_spj');
    });

    //   manage problem tag
    Route::middleware(['Privilege:admin.problem.tag'])->prefix('problem')->name('problem.')->group(function () {
        Route::get('/tags', 'Admin\ProblemController@tags')->name('tags');
        Route::post('/tag_delete', 'Admin\ProblemController@tag_delete')->name('tag_delete');
        Route::get('/tag_pool', 'Admin\ProblemController@tag_pool')->name('tag_pool');
        Route::post('/tag_pool_delete', 'Admin\ProblemController@tag_pool_delete')->name('tag_pool_delete');
        Route::post('/tag_pool_hidden', 'Admin\ProblemController@tag_pool_hidden')->name('tag_pool_hidden');
    });

    //   manage problem data
    Route::middleware(['Privilege:admin.problem.data'])->prefix('problem')->name('problem.')->group(function () {
        Route::get('/test_data', 'Admin\ProblemController@test_data')->name('test_data');
        Route::post('/upload/data', 'Admin\ProblemController@upload_data')->name('upload_data');
        Route::post('/get_data', 'Admin\ProblemController@get_data')->name('get_data');
        Route::post('/update/data', 'Admin\ProblemController@update_data')->name('update_data');
        Route::post('/delete/data', 'Admin\ProblemController@delete_data')->name('delete_data');
    });

    //   manage problem rejudge
    Route::middleware(['Privilege:admin.problem.rejudge'])->prefix('problem')->name('problem.')->group(function () {
        Route::any('/rejudge', 'Admin\ProblemController@rejudge')->name('rejudge');
    });

    //   manage problem import export
    Route::middleware(['Privilege:admin.problem.import_export'])->prefix('problem')->name('problem.')->group(function () {
        Route::get('/import_export', 'Admin\ProblemController@import_export')->name('import_export');
        Route::any('/import', 'Admin\ProblemController@import')->name('import');
        Route::any('/export', 'Admin\ProblemController@export')->name('export');
    });

    //   manage contest
    Route::middleware(['Privilege:admin.contest'])->prefix('contest')->name('contest.')->group(function () {
        Route::get('/list', 'Admin\ContestController@list')->name('list');
        Route::any('/add', 'Admin\ContestController@add')->name('add');
        Route::any('/update/{id}', 'Admin\ContestController@update')->name('update');
        Route::post('/delete', 'Admin\ContestController@delete')->name('delete');
        Route::post('/delete/file/{id}', 'Admin\ContestController@delete_file')->name('delete_file');
        Route::post('/update/hidden', 'Admin\ContestController@update_hidden')->name('update_hidden');
        Route::post('/update/public_rank', 'Admin\ContestController@update_public_rank')->name('update_public_rank');
        Route::post('/clone', 'Admin\ContestController@clone')->name('clone');
        // Route::post('/set_top','Admin\ContestController@set_top')->name('set_top');
        Route::post('/update_order', 'Admin\ContestController@update_order')->name('update_order');
        Route::post('/update_contest_cate_id', 'Admin\ContestController@update_contest_cate_id')->name('update_contest_cate_id');

        Route::middleware(['Privilege:admin.contest.category'])->group(function () {
            Route::get('/categories', 'Admin\ContestController@categories')->name('categories');
            Route::post('/update_cate', 'Admin\ContestController@update_cate')->name('update_cate');
            Route::post('/update_cate_order', 'Admin\ContestController@update_cate_order')->name('update_cate_order');
            Route::post('/delete_cate/{id}', 'Admin\ContestController@delete_cate')->name('delete_cate');
        });
    });

    // manage group
    Route::middleware(['Privilege:admin.group'])->prefix('group')->name('group.')->group(function () {
        Route::get('/list', 'Admin\GroupController@list')->name('list');
        Route::any('/edit', 'Admin\GroupController@edit')->name('edit');
        Route::get('/delete/{id}', 'Admin\GroupController@delete')->name('delete');
        Route::post('/add_member/{id}', 'Admin\GroupController@add_member')->name('add_member');
        Route::get('/del_member/{id}/{uid}', 'Admin\GroupController@del_member')->name('del_member');
        Route::get('/member_iden/{id}/{uid}/{iden}', 'Admin\GroupController@member_iden')->name('member_iden');
    });

    // setting
    Route::middleware(['Privilege:admin.setting'])->group(function () {
        Route::any('/settings', 'Admin\SettingController@settings')->name('settings');
        Route::get('/upgrade', 'Admin\SettingController@upgrade')->name('upgrade');
        Route::post('/upgrade_oj', 'Admin\SettingController@upgrade_oj')->name('upgrade_oj');
    });
});
