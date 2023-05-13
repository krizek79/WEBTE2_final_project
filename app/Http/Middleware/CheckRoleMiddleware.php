<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomException;
use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Auth;

class CheckRoleMiddleware extends Authenticate
{
    /**
     * @throws CustomException
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            throw new CustomException('Unauthenticated', 401);
        }

        foreach ($roles as $role) {
            if ($user->role === $role) {
                Auth::shouldUse($role);
                return $next($request);
            }
        }

        throw new CustomException('Unauthorized', 403);
    }
}
