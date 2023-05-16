<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\File;
use App\Services\FileService;

class FileController extends Controller
{

    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }


    /**
     * @throws CustomException
     */
    public function index(): JsonResponse
    {
        $result = $this->fileService->getFiles();
        return response()->json($result);
    }

    /**
     * @throws CustomException
     */
    public function getAccessibleFiles(): JsonResponse
    {
        $result = $this->fileService->getAccessibleFiles();

        return response()->json($result);
    }

    /**
     * @throws CustomException
     */
    public function updateFilePoints(Request $request): JsonResponse
    {
        $result = $this->fileService->updateFilePoints($request);
        return response()->json($result);
    }

    public function updateAccessibility(Request $request)
    {
        $result = $this->fileService->updateAccessibility($request);
        return response()->json($result, 200);
    }

    /**
     * @throws CustomException
     */
    public function updateAccessibilityTime(Request $request): JsonResponse
    {
        $result = $this->fileService->updateAccessibilityTime($request);
        return response()->json($result);
    }

}
