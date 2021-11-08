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

Route::get('/cektoken', 'AuthController@checktoken');

Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('register', 'AuthController@register');    
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
});

Route::group(['prefix' => 'user'], function ($router) {
    Route::get('/search', 'UserController@searchUser');
    Route::get('/{username}/profile', 'UserController@profile');
    Route::post('/profile', 'UserController@setup');
    
    Route::get('/schedules', 'ScheduleController@getUserSchedule');
    Route::get('/tasks', 'TaskController@getUserTask');
    Route::get('/friends', 'FriendController@getUserFriend');
});

Route::group(['prefix' => 'friend'], function ($router) {
    Route::post('/invite', 'FriendController@invite');
    Route::post('/accept', 'FriendController@accept');
    Route::post('/decline', 'FriendController@decline');
    Route::delete('/delete', 'FriendController@delete');   
});

Route::apiResource('schedules', 'ScheduleController');
Route::apiResource('tasks', 'TaskController');
Route::apiResource('todos', 'TodoController');
