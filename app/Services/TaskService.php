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

        // Get the data from the request
        $requestData = $request->all();

        // Array to store all tasks
        $allTasks = collect();

        foreach ($requestData as $data) {
            $fileId = $data['file'];
            $numTasks = $data['num'];

            // Fetch the requested number of random tasks associated with the given file
            $tasksQuery = Task::whereHas('file', function($query) use ($now, $fileId) {
                $query->where('file_id', $fileId)
                    ->where('is_accessible', true)
                    ->where(function($query) use ($now) {
                        $query->where(function($query) use ($now) {
                            $query->where('accessible_from', '<=', $now)
                                ->where('accessible_to', '>=', $now);
                        })
                        ->orWhereNull('accessible_from')
                        ->orWhereNull('accessible_to');
                    });
            });

            $studentId = $request->user()->id;
            $studentId = 1;

            $generatedTaskIds = GeneratedTask::where('student_id', $studentId)->pluck('task_id')->toArray();

            $tasksQuery = $tasksQuery->whereNotIn('id', $generatedTaskIds);

            $tasks = $tasksQuery->inRandomOrder()
                ->take($numTasks)
                ->get();

            // Create a new GeneratedTask for each task and associate it with the current student
            foreach ($tasks as $task) {
                $generatedTask = new GeneratedTask;
                $generatedTask->student_id = $studentId;
                $generatedTask->task_id = $task->id;
                $generatedTask->correctness = 'NOT_EVALUATED';
                $generatedTask->save();
            }

            // Append the tasks to the allTasks collection
            $allTasks = $allTasks->concat($tasks);
        }

        if ($allTasks->isEmpty()) {
            throw new CustomException("No accessible tasks found in all requested files", 404);
        }

        return ($allTasks->map(function ($task) {
            return [
                'id' => $task->id,
                'task' => $task->task,
                'image' => $task->image,
                'points' => $task->file->points,
                'file_name' => $task->file->file_name
            ];
        }));
    }
}
