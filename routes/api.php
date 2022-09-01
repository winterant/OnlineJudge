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
        Route::middleware(['auth:api', 'CheckBlacklist'])->group(function () {
            Route::post('/solution/submit','SolutionController@submit')->name('submit');
            Route::get('/solution/result', 'SolutionController@result')->name('result');
        });
        Route::post('/solution/judge0_callback/{solution_id}', 'SolutionController@judge0_callback')->name('judge0_callback');
    });
});
