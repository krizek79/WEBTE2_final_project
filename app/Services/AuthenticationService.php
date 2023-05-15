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
    public function authenticate(Request $request): array
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('email',$request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw new CustomException("Wrong credentials", 401);
        }

        $token=$user->createToken('authToken')->accessToken;

        return [
            'token'=> $token,
            'user' => $user->email,
            'role' => $user->role
        ];
    }

    /**
     * @throws CustomException
     */
    public function register(Request $request): array
    {
        if (is_null($request->email) || is_null($request->password)
            || is_null($request->firstName) || is_null($request->lastName)
            || is_null($request->matchingPassword || is_null($request->role))
        ) {
            throw new CustomException("Missing parameter(s)", 400);
        }

        $userExists = User::where('email', $request->email)->exists();
        if ($userExists) {
            throw new CustomException("User with this email already exists", 400);
        }

        if ($request->password != $request->matchingPassword) {
            throw new CustomException("Password and matching password do not match", 400);
        }

        $user = User::create([
            'email' => $request->email,
            'first_name' => $request->firstName,
            'last_name' => $request->lastName,
            'password' => $request->password,
            'role' => $request->role
        ]);

        return [
            'message' => "Registration successful",
            'email' => $user->email
        ];
    }
}
