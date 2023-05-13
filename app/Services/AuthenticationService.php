<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use function PHPUnit\Framework\isNan;

class AuthenticationService
{

    /**
     * @throws CustomException
     */
    public function authenticate(Request $authRequest): array
    {
        $authRequest->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('email',$authRequest->email)->first();

        if (!$user || !Hash::check($authRequest->password, $user->password)) {
            throw new CustomException("Wrong credentials", 401);
        }

        $token=$user->createToken('authToken')->accessToken;

        return [
            'token'=>$token
        ];
    }

    /**
     * @throws CustomException
     */
    public function register(Request $registrationRequest): array
    {
        if (is_null($registrationRequest->email) || is_null($registrationRequest->password)
            || is_null($registrationRequest->matchingPassword || is_null($registrationRequest->role))
        ) {
            throw new CustomException("Missing parameter(s)", 400);
        }

        $userExists = User::where('email', $registrationRequest->email)->exists();
        if ($userExists) {
            throw new CustomException("User with this email already exists", 400);
        }

        if ($registrationRequest->password != $registrationRequest->matchingPassword) {
            throw new CustomException("Password and matching password do not match", 400);
        }

        $user = User::create([
            'email' => $registrationRequest->email,
            'password' => $registrationRequest->password,
            'role' => $registrationRequest->role
        ]);

        return [
            'message' => "Registration successful",
            'email' => $user->email
        ];
    }
}
