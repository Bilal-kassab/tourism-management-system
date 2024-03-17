<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('register',[UserController::class,'register']);
Route::post('login',[UserController::class,'login']);

Route::post('confirm-Code',[UserController::class,'confirmCode']);
Route::post('forget-password',[UserController::class,'forgetPassword_SendEmail']);
Route::post('set-new-password',[UserController::class,'forgetPassword_SetPassword']);


Route::group(['middleware'=>['auth:sanctum','verfiy-code']], function () {
    /*
    Route::get('check', function () {
        return response()->json([ 
            'Message'=> 'Check',
        ],200);
    });
    */

    Route::get('logout',[UserController::class,'logout']);
    Route::get('profile',[UserController::class,'profile']);

    

});


Route::post('add-admin',[AdminController::class,'addAdmin']);
Route::post('admin-login',[AdminController::class,'login']);

Route::group(['middleware'=>['auth:sanctum']],function(){

    Route::get('admin-profile',[AdminController::class,'profile']);
    Route::get('admin-logout',[AdminController::class,'logout']);

});