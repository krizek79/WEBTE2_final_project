<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
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
    public function getTasksByStudent($id): JsonResponse
    {
        $result = $this->generatedTaskService->getTasksByStudent($id);
        return response()->json($result, 200);
    }

    /**
     * @throws CustomException
     */
    public function updateStudentAnswer(Request $request, $id): JsonResponse
    {
        $result = $this->generatedTaskService->updateStudentAnswer($request, $id);
        return response()->json($result, 200);
    }

    /**
     * @throws CustomException
     */
    public function getTaskListByStudent(Request $request): JsonResponse
    {
        $result = $this->generatedTaskService->getTaskListByStudent($request);
        return response()->json($result, 200);
    }


    /**
     * @throws CustomException
     */
    public function getStudentsResults(): JsonResponse
    {
        $result = $this->generatedTaskService->getStudentsResults();
        return response()->json($result);
    }

}
