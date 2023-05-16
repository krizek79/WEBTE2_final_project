<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\Task;
use App\Models\GeneratedTask;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TaskService
{

    /**
     * @throws CustomException
     */
    public function getAllTasks()
    {
         $tasks = Task::with('file')->get();

        return ($tasks->map(function ($task) {
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

    /**
     * @param int $taskId
     * @return array
     * @throws CustomException
     */
    public function getTaskById($taskId)
    {
        $task = Task::with('file')->find($taskId);

        if (!$task) {
            throw new CustomException('Task not found');
        }

        return [
            'id' => $task->id,
            'task' => $task->task,
            //'solution' => $task->solution,
            'image' => $task->image,
            'points' => $task->file->points,
            'file_name' => $task->file->file_name
        ];
    }

    /**
     * @throws CustomException
     */
    public function generateTasks(Request $request)
    {
        $now = Carbon::now();

        $fileNames = $request->get('file');

        $numTasks = $request->get('num', 3); // default to 3 if not provided

        //$student = Auth::user(); 

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
            throw new CustomException("No accessible tasks found", 404);
        }

        // Create a new GeneratedTask for each task and associate it with the current student
        foreach ($tasks as $task) {
            $generatedTask = new GeneratedTask;
            $generatedTask->student_id = 1/*$student->id*/;
            $generatedTask->task_id = $task->id;
            $generatedTask->correctness = 'NOT_EVALUATED';
            $generatedTask->save();
        }

        return ($tasks->map(function ($task) {
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
