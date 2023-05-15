<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{

    protected AuthenticationService $authenticationService;

    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }


    /**
     * @throws CustomException
     */
    public function authenticate(Request $request): JsonResponse
    {
        $result = $this->authenticationService->authenticate($request);
        return response()->json($result);
    }

    /**
     * @throws CustomException
     */
    public function register(Request $request): JsonResponse
    {
        $result = $this->authenticationService->register($request);
        return response()->json($result);
    }

}
