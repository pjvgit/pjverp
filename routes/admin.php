<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/**
 * Guest routes
 */

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['guest:admin'], 'namespace' => 'Admin'], function () {
    Route::get("login", "AuthController@showLoginForm")->name('admin/login');
    Route::post("login", "AuthController@login")->name('admin/login/post');
    Route::post('logout', 'AuthController@logout')->name('admin/logout');
});

/**
 * After login routes
 */
Route::group(['middleware' => [/* 'admin', */ 'auth:admin'], 'namespace' => 'Admin'], function () {
    Route::get("/", "DashboardController@index")->name('admin/dashboard');
});