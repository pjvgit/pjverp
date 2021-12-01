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
Route::group(['namespace' => 'Admin', 'prefix' => 'admin'], function () {
    Route::get("login", "AuthController@showLoginForm")->name('admin/login');
    Route::post("login", "AuthController@login")->name('admin/login');
});

/**
 * After login routes
 */
Route::group(['namespace' => 'Admin', 'prefix' => 'admin'], function () {
    Route::get("dashboard", "DashboardController@index")->name('admin/dashboard');
});