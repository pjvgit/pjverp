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
    Route::get("/dashboard", "DashboardController@index")->name('admin/dashboard');
    Route::post("/searchUsers", "DashboardController@searchUsers")->name('admin/searchUsers');
    Route::get("/userlist", "UserController@index")->name('admin/userlist');
    Route::get("/userinfo/{id}", "UserController@userInfo")->name('admin/userinfo');
    Route::get("/loadusers", "UserController@loadUsers")->name('admin/loadusers');
    Route::get("/stafflist", "UserController@staffList")->name('admin/stafflist');
    Route::get("/stafflist/info/{id}", "UserController@staffInfo")->name('admin/stafflist/info');
    Route::get("/stafflist/info/{id}/cases", "UserController@staffInfo")->name('admin/stafflist/cases');
    Route::get("/loadstaff", "UserController@loadStaff")->name('admin/loadstaff');
    Route::get('/stafflist/loadStaffCase', 'UserController@staffCaseList')->name('admin/stafflist/loadStaffCase');
});