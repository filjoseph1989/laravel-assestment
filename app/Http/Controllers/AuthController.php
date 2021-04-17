<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register administrator
     * @param  Request $request
     * @return  string
     */
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name'      => 'required|string',
            'user_name' => 'required|string',
            'email'     => 'required|string|unique:users,email',
            'password'  => 'required|string|confirmed',
            'avatar'    => 'mimes:png,jpg|dimensions:min_width=256,min_height=256' # Task-15
        ]);

        $file = $request->avatar->store('public/avatar');

        $user = User::create([
            'name'              => $fields['name'],
            'user_name'         => $fields['user_name'],
            'email'             => $fields['email'],
            'password'          => bcrypt($fields['password']),
            'registered_at'     => date("Y-m-d H:i:s"),
            'email_verified_at' => date("Y-m-d H:i:s"),
            'user_role'         => 'admin',
            'avatar'            => $file
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

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
