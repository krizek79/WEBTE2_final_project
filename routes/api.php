<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Middleware\CheckRoleMiddleware;
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

//  Authentication
Route::post("/authenticate", [AuthenticationController::class, "authenticate"]);
Route::post("/register", [AuthenticationController::class, "register"]);

//  Authenticated endpoints
Route::group(['middleware' => ['auth:api']], function() {
    Route::group(['middleware' => [CheckRoleMiddleware::class . ':teacher']], function() {
        Route::get('/teachers', function () {
            return "Hello teacher";
        });
    });

    Route::group(['middleware' => [CheckRoleMiddleware::class . ':student']], function() {
        Route::get('/students', function () {
            return "Hello student";
        });
    });
});
