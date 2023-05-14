<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\GeneratedTask;

class TaskController extends Controller
{

    public function getFileNames()
    {
        // Fetch all unique file_names from the tasks table
        $fileNames = Task::distinct('file_name')->pluck('file_name');

        // Return the file names
        return response()->json($fileNames);
    }

    /**
     * Display a random accessible task.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTask()
    {
        // Get the current time
        $now = Carbon::now();

        // Fetch a random task that is accessible and the current time is between accessible_from and accessible_to
        $task = Task::where('is_accessible', true)
                    ->where(function($query) use ($now) {
                        $query->where(function($query) use ($now) {
                            $query->where('accessible_from', '<=', $now)
                                  ->where('accessible_to', '>=', $now);
                        })
                        ->orWhereNull('accessible_from')
                        ->orWhereNull('accessible_to');
                    })
                    ->inRandomOrder()
                    ->first();

        // If no task was found, return a 404 response
        if (!$task) {
            return response()->json(['error' => 'No accessible task found'], 404);
        }

        // Return the task
        return response()->json($task->only('id', 'task', 'solution', 'image', 'points', 'file_name'));
    }



    public function getTasks(Request $request)
    {
        $now = Carbon::now();

        $fileNames = $request->get('file');

        // Get the number of tasks to return from the request
        $numTasks = $request->get('num', 3); // default to 3 if not provided

        // Fetch the requested number of random tasks that are accessible and the current time is between accessible_from and accessible_to
        $tasksQuery = Task::where('is_accessible', true)
                    ->where(function($query) use ($now) {
                        $query->where(function($query) use ($now) {
                            $query->where('accessible_from', '<=', $now)
                                ->where('accessible_to', '>=', $now);
                        })
                        ->orWhereNull('accessible_from')
                        ->orWhereNull('accessible_to');
                    });

        // If fileNames is provided, add it to the query
        if($fileNames) {
            if(!is_array($fileNames)){
                $fileNames = [$fileNames];
            }
            $tasksQuery->whereIn('file_name', $fileNames);
        }
                    
        $tasks = $tasksQuery->inRandomOrder()
                    ->take($numTasks)
                    ->get();

        // If no tasks were found, return a 404 response
        if ($tasks->isEmpty()) {
            return response()->json(['error' => 'No accessible tasks found'], 404);
        }

        // Create a new GeneratedTask for each task and associate it with the current student
        $student = Auth::user(); // get the currently authenticated user (student)
        foreach ($tasks as $task) {
            $generatedTask = new GeneratedTask;
            $generatedTask->student_id = 1; //$student->id;
            $generatedTask->task_id = $task->id;
            $generatedTask->correctness = 'NOT_EVALUATED';
            $generatedTask->save();
        }

        // Return only the id, task, solution, image, points, and file_name fields of the tasks
        return response()->json($tasks->map(function ($task) {
            return $task->only('id', 'task', 'solution', 'image', 'points', 'file_name');
        }));
    }


}
