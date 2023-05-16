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
     * @throws CustomException
     */
    public function getAllTasks(): JsonResponse
    {
        $result = $this->taskService->getAllTasks();
        return response()->json($result);
    }

    /**
     * @throws CustomException
     */
    public function getTaskById($id): JsonResponse
    {
        $result = $this->taskService->getTaskById($id);
        return response()->json($result);
    }

    /**
     * @throws CustomException
     */
    public function generateTasks(Request $request)
    {
        $result = $this->taskService->generateTasks($request);
        return response()->json($result);
    }
}
