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

Route::group(['prefix' => 'dashboard'], function ($router) {
    Route::post('task', 'DashboardController@sortTas');
    Route::post('schedule', 'DashboardController@sortSchedule');
});

Route::group(['prefix' => 'user'], function ($router) {
    Route::get('/{username}/profile', 'AuthController@profile');
    Route::post('/{username}/profile', 'UserController@setup');
    Route::get('/{username}/schedules', 'ScheduleController@getUserSchedule');
    Route::get('/{username}/tasks', 'TaskController@getUserTask');
});

Route::apiResource('schedules', 'ScheduleController');
Route::apiResource('tasks', 'TaskController')->middleware('verified');
Route::apiResource('todos', 'TodoController');
