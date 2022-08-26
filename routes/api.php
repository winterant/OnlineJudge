<?php

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
    Route::middleware(['auth:api', 'CheckBlacklist'])->name('solution.')->where(['id' => '[0-9]+'])->group(function () {
        Route::post('/submit_solution','SolutionController@submit')->name('submit');
    });
});
