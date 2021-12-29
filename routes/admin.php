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
    Route::post("/loadallstaffdata", "UserController@loadAllStaffData")->name('admin/loadallstaffdata');
    Route::post("/checkStaffDetails", "UserController@checkStaffDetails")->name('admin/checkStaffDetails');
    
    // user management
    Route::get("/users", "UserController@index")->name('admin/users');
    Route::get("/userlist", "UserController@userList")->name('admin/userlist');
    Route::get("/userlist/info/{id}", "UserController@userInfo")->name('admin/userlist/info');
    Route::get("/userlist/info/{id}/cases", "UserController@userInfo")->name('admin/userlist/cases');
    Route::get("/loadusers", "UserController@loadUsers")->name('admin/loadusers');    
    Route::get("/stafflist", "UserController@staffList")->name('admin/stafflist');
    Route::get("/stafflist/info/{id}", "UserController@staffInfo")->name('admin/stafflist/info');
    Route::get("/stafflist/info/{id}/cases", "UserController@staffInfo")->name('admin/stafflist/cases');
    Route::get("/stafflist/info/{id}/staff", "UserController@firmStaffList")->name('admin/stafflist/staff');
    Route::get("/loadstaff", "UserController@loadStaff")->name('admin/loadstaff');
    Route::get("/loadFirmStaffList", "UserController@loadFirmStaffList")->name('admin/loadFirmStaffList');
    Route::get('/stafflist/loadStaffCase', 'UserController@staffCaseList')->name('admin/stafflist/loadStaffCase');
    Route::post("/reactivateStaff", "UserController@reactivateStaff")->name('admin/reactivateStaff');
    Route::post("/deactivateStaff", "UserController@deactivateStaff")->name('admin/deactivateStaff');
    Route::post("/loadDeactivateUser", "UserController@loadDeactivateUser")->name('admin/loadDeactivateUser');
    Route::get("/exportAllStaff", "UserController@exportAllStaff")->name('admin/exportAllStaff');
    
    // admin profile
    Route::get("/loadProfile", "UserController@loadProfile")->name('admin/loadProfile');
    Route::post("/saveProfile", "UserController@saveProfile")->name('admin/saveProfile');
    Route::post("/savePassword", "UserController@savePassword")->name('admin/savePassword');
    
});