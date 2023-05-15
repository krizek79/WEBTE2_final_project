<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\GeneratedTask;

class TaskController extends Controller
{
    /**
     * Display a random accessible task.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTask()
    {
        $now = Carbon::now();

        // Fetch a random task associated with an accessible file
        $task = Task::whereHas('file', function($query) use ($now) {
            $query->where('is_accessible', true)
                ->where(function($query) use ($now) {
                    $query->where(function($query) use ($now) {
                        $query->where('accessible_from', '<=', $now)
                            ->where('accessible_to', '>=', $now);
                    })
                    ->orWhereNull('accessible_from')
                    ->orWhereNull('accessible_to');
                });
        })
        ->inRandomOrder()
        ->first();

        if (!$task) {
            return response()->json(['error' => 'No accessible task found'], 404);
        }

        return response()->json([
            'id' => $task->id,
            'task' => $task->task,
            'solution' => $task->solution,
            'image' => $task->image,
            'points' => $task->file->points,
            'file_name' => $task->file->file_name
        ]);
    }

    public function getAllTasks()
    {
         $tasks = Task::with('file')->get();

        return response()->json($tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'task' => $task->task,
                'solution' => $task->solution,
                'image' => $task->image,
                'points' => $task->file->points,
                'file_name' => $task->file->file_name
            ];
        }));
    }

    public function generateTasks(Request $request)
    {
        $now = Carbon::now();

        $fileNames = $request->get('file');

        $numTasks = $request->get('num', 3); // default to 3 if not provided

        $student = Auth::user(); 

        $generatedTaskIds = GeneratedTask::where('student_id', 1/*$student->id*/)->pluck('task_id')->toArray();

        // Fetch the requested number of random tasks associated with accessible files and not in $generatedTaskIds
        $tasksQuery = Task::whereHas('file', function($query) use ($now, $fileNames) {
            $query->where('is_accessible', true)
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
                $query->whereIn('file_name', $fileNames);
            }
        })
        ->whereNotIn('id', $generatedTaskIds);
                        
        $tasks = $tasksQuery->inRandomOrder()
                    ->take($numTasks)
                    ->get();

        if ($tasks->isEmpty()) {
            return response()->json(['error' => 'No accessible tasks found'], 404);
        }

        // Create a new GeneratedTask for each task and associate it with the current student
        foreach ($tasks as $task) {
            $generatedTask = new GeneratedTask;
            $generatedTask->student_id = 1/*$student->id*/;
            $generatedTask->task_id = $task->id;
            $generatedTask->correctness = 'NOT_EVALUATED';
            $generatedTask->save();
        }

        return response()->json($tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'task' => $task->task,
                //'solution' => $task->solution,
                'image' => $task->image,
                'points' => $task->file->points,
                'file_name' => $task->file->file_name
            ];
        }));
    }




}
