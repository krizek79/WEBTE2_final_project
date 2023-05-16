<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\GeneratedTask;
use App\Services\GeneratedTaskService;


class GeneratedTaskController extends Controller
{
    protected GeneratedTaskService $generatedTaskService;

    public function __construct(GeneratedTaskService $generatedTaskService)
    {
        $this->generatedTaskService = $generatedTaskService;
    }

    /**
     * @throws CustomException
     */
    public function getTasksByStudent(): JsonResponse
    {
        $result = $this->generatedTaskService->getTasksByStudent();
        return response()->json($result, 200);
    }

    /**
     * @throws CustomException
     */
    public function updateStudentAnswer(Request $request, $taskId): JsonResponse
    {
        $result = $this->generatedTaskService->updateStudentAnswer($request, $taskId);
        return response()->json($result, 200);
    }


    /**
     * @throws CustomException
     */
    public function getResults(): JsonResponse
    {
        $result = $this->generatedTaskService->getResults();
        return response()->json($result, 200);
    }

}
