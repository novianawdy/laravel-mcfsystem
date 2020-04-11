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
    Route::get('/', 'UserController@index')->middleware('throttle:360,1');
    Route::get('logout', 'UserController@logout');
    Route::get('show', 'UserController@show');
    Route::post('register', 'UserController@register')->middleware('role:1');
    Route::put('/', 'UserController@update');
    Route::put('/change-password', 'UserController@changePassword');
});

Route::group(['middleware' => ['auth:api'], 'prefix' => 'logs'], function () {
    Route::get('/', 'LogController@index')->middleware('throttle:360,1');
    Route::post('/', 'LogController@store');
});

Route::group(['middleware' => ['auth:api'], 'prefix' => 'settings'], function () {
    Route::get('/', 'SettingController@index');
    Route::put('/', 'SettingController@update');
    Route::put('/bulk-update', 'SettingController@bulkUpdate');
    Route::put('/bulk-update-mix', 'SettingController@bulkUpdateMix');
});

Route::group(['middleware' => ['auth:api'], 'prefix' => 'notifications'], function () {
    Route::get('/', 'NotificationController@index')->middleware('throttle:360,1');
    Route::get('/mark-all-as-read', 'NotificationController@markAllAsRead');
    Route::post('/mark-as-read', 'NotificationController@markAsRead');
});
