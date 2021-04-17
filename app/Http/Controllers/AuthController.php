<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Traits\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use Registration;

    /**
     * Login a user
     * @param  Request $request
     * @return  string
     */
    public function login(Request $request)
    {
        # Task-14
        $fields = $request->validate([
            'email'     => 'required|string',
            'password'  => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response(['message' => "Sorry we couldn't log you in!"], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    /**
     * Logout the user
     * @param  Request $request
     */
    public function logout(Request $request): array
    {
        auth()->user()->tokens()->delete();
        return ['message' => 'logged out successfully'];
    }
}
