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
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
});

Route::get('/user', 'AuthController@user');

Route::group(['prefix' => 'task'], function ($router) {
    Route::post('', 'TaskController@create')->middleware('jwt.verify');
    Route::put('/{id}', 'TaskController@update')->middleware('jwt.verify');
    Route::delete('/{id}', 'TaskController@delete')->middleware('jwt.verify');
});