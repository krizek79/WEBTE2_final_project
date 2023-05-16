<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Task;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\GeneratedTask;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }


    /**
     * Display a random accessible task.
     *
     * @return \Illuminate\Http\Response
     */
    /*public function getTask()
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
    }*/

    public function getAllTasks(): JsonResponse
    {
        $result = $this->taskService->getAllTasks();
        return response()->json($result);
    }

    public function generateTasks(Request $request)
    {
        $result = $this->taskService->generateTasks($request);
        return response()->json($result);
    }
}
