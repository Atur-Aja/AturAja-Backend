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

// For API Health Check
Route::get('/', function () {
    return response()->json();
});

Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login')->middleware('checkuserisactive');
//    Route::post('login', 'AuthController@login');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('logout', 'AuthController@logout');
});

Route::group(['prefix' => 'dashboard'], function ($router) {
    Route::post('task', 'DashboardController@sortTas');
    Route::post('schedule', 'DashboardController@sortSchedule');
});

Route::group(['prefix' => 'user'], function ($router) {
    Route::get('/search', 'UserController@searchUser');
    Route::get('/{username}/profile', 'UserController@profile');
    Route::post('/profile', 'UserController@setup');

    Route::get('/schedules', 'ScheduleController@getUserSchedule');
    Route::get('/tasks', 'TaskController@getUserTask');

    Route::get('/friends', 'FriendController@getUserFriends');
    Route::post('/friends', 'FriendController@getFriendsByUsername');
    Route::get('/friendsreq', 'FriendController@getFriendsReq');
    Route::get('/friendsreqsent', 'FriendController@getFriendsReqSent');

    Route::get('/image/{filename}', 'PhotoController@image');
});

Route::group(['prefix' => 'friend'], function ($router) {
    Route::post('/invite', 'FriendController@invite');
    Route::post('/accept', 'FriendController@accept');
    Route::post('/decline', 'FriendController@decline');
    Route::delete('/delete', 'FriendController@delete');
});

Route::group(['prefix' => 'tasks'], function ($router) {
    Route::post('/add', 'TaskCollaboration@add');
    Route::get('/see', 'TaskCollaboration@see');
    Route::delete('/remove', 'TaskCollaboration@remove');
    Route::delete('/update', 'TaskCollaboration@update');
});

Route::group(['prefix' => 'schedules'], function ($router) {
    Route::post('/update/{id}', 'UpdateScheduleController@perbarui');
});

Route::get('email/verify/{id}', 'VerificationController@verify')->name('verification.verify');
Route::post('email/resend', 'VerificationController@resendEmail')->name('verification.resend');

Route::apiResource('schedules', 'ScheduleController');
Route::post('schedules/match', 'ScheduleController@matchSchedule');
Route::apiResource('tasks', 'TaskController');
Route::apiResource('todos', 'TodoController');
