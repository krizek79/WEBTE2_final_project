<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Middleware\CheckRoleMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\GeneratedTaskController;

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
    //  Teacher
    Route::group(['middleware' => [CheckRoleMiddleware::class . ':teacher']], function() {
        Route::get('/teachers', function () {
            return "Hello teacher";
        });
    });

    //  Student
    Route::group(['middleware' => [CheckRoleMiddleware::class . ':student']], function() {
        Route::get('/students', function () {
            return "Hello student";
        });
    });
});

Route::get('/files', [TaskController::class, 'getFileNames']);
Route::get('/task', [TaskController::class, 'getTask']);
Route::get('/tasks', [TaskController::class, 'getTasks']);
Route::get('/student/tasks', [GeneratedTaskController::class, 'getTasksByStudent']);
