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
        Route::prefix('teachers')->group(function () {
            //Return all files in database
            Route::get('/files', [FileController::class, 'index']);
            //Return data for teacher's table
            Route::get('/tasks/statistics', [GeneratedTaskController::class, 'getStudentsResults']);
            //Return task by id
            Route::get('/tasks/{id}', [TaskController::class, 'getTaskById']);
            //Return list of student's tasks by student id, FOR TEACHER with solutions and student answers
            Route::get('/tasks/student/{id}', [GeneratedTaskController::class, 'getTasksByStudent']);
            //Update files settings
            Route::patch('/files/setting', [FileController::class, 'updateFileSetting']);
        });
    });

    // Student
    Route::group(['middleware' => [CheckRoleMiddleware::class . ':student']], function() {
        Route::prefix('students')->group(function () {
            //  Return files that are accessible to the student
            Route::get('/files/accessible', [FileController::class, 'getAccessibleFiles']);
            //  Return list of student's tasks for "example list", logged in student
            Route::get('/tasks', [GeneratedTaskController::class, 'getTaskListByStudent']);
            //  Return task by id
            Route::get('/tasks/{id}', [TaskController::class, 'getTaskById']);
            //  Generates and returns for the student tasks that are accessible
            //  to him and at the same time adds a record to the table generated_tasks
            Route::post('/tasks/generate', [TaskController::class, 'generateTasks']);
            //  Saves and checks the student's answer
            Route::patch('/tasks/{id}/submit', [GeneratedTaskController::class, 'updateStudentAnswer']);
        });

    });
});

//Return all files in database
Route::get('/files', [FileController::class, 'index']);

//Return all tasks in database
Route::get('/tasks', [TaskController::class, 'getAllTasks']);

