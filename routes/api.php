<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Api Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Api routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your Api!
|
*/

use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function (){

    // Contest Api
    Route::prefix('contests')->name('contests.')->group(function (){
        //管理员api
        Route::prefix('/manage')->name('manage.')->middleware(['auth','CheckContest','CheckBlacklist','Privilege:contest'])->group(function (){
            //调整order；需提供两个字段：contest1_id，contest2_id，表示要交换的竞赛id
            Route::post('/exchange_order', 'Api\ContestController@exchange_order')->name('exchange_order');
            //竞赛在自己的内别内位置移动到最顶端，需提供一个字段: contest_id
            Route::post('/order_to_top', 'Api\ContestController@order_to_top')->name('order_to_top');
        });
    });

});
