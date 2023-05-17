<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Middleware\CheckRoleMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
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
            //Route::get('/files', [FileController::class, 'index']);
        });
    });
});

//Return all files in database
Route::get('/files', [FileController::class, 'index']);

//Return files that are accessible to the student
Route::get('/files/accessible', [FileController::class, 'getAccessibleFiles']);

//Return all tasks in database
Route::get('/tasks', [TaskController::class, 'getAllTasks']);

//Generates and returns for the student tasks that are accessible to him and at the same time adds a record to the table generated_tasks
Route::get('/tasks/generate', [TaskController::class, 'generateTasks']);

//Return task by id
Route::get('/tasks/{id}', [TaskController::class, 'getTaskById']);


//Return list of student's tasks for "example list", logged in student
Route::get('/generatedtasks/examplelist', [GeneratedTaskController::class, 'getExampleList']);

//Return data for teacher's table
Route::get('/generatedtasks/results', [GeneratedTaskController::class, 'getStudentsResults']);

//Return list of student's tasks by student id, FOR TEACHER with solutions and student answers
Route::get('/generatedtasks/{id}', [GeneratedTaskController::class, 'getTasksByStudent']);

//Saves and checks the student's answer
Route::patch('/generatedtasks/{id}/answer', [GeneratedTaskController::class, 'updateStudentAnswer']);

Route::patch('/files/setting', [FileController::class, 'updateFileSetting']);
Route::patch('/files/points', [FileController::class, 'updateFilePoints']);
Route::patch('/files/{fileName}/access', [FileController::class, 'updateAccessibility']);
Route::patch('/files/{fileName}/accesstime', [FileController::class, 'updateAccessibilityTime']);
