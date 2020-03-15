<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'UserController@login');
Route::group(['middleware' => ['auth:api'], 'prefix' => 'user'], function () {
    Route::get('/', 'UserController@index');
    Route::get('logout', 'UserController@logout');
    Route::get('show', 'UserController@show');
    Route::post('register', 'UserController@register')->middleware('role:1');
    Route::put('/', 'UserController@update');
    Route::put('/change-password', 'UserController@changePassword');
});

Route::group(['middleware' => ['auth:api'], 'prefix' => 'logs'], function () {
    Route::get('/', 'LogController@index');
    Route::post('/', 'LogController@store');
});

Route::group(['middleware' => ['auth:api'], 'prefix' => 'settings'], function () {
    Route::get('/', 'SettingController@index');
    Route::put('/', 'SettingController@update');
    Route::put('/bulk-update', 'SettingController@bulkUpdate');
});
