<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/files', [TaskController::class, 'getFileNames']);
Route::get('/task', [TaskController::class, 'getTask']);
Route::get('/tasks', [TaskController::class, 'getTasks']);
Route::get('/student/tasks', [GeneratedTaskController::class, 'getTasksByStudent']);
