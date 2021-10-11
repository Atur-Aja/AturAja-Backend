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
    Route::get('', 'AuthController@user');
    Route::get('/{username}/schedules', 'ScheduleController@getUserSchedule');
});

Route::apiResource('schedules', 'ScheduleController');

// Route::group(['prefix' => 'schedules'], function ($router) {    
//     Route::apiResource('', 'ScheduleController');
// });