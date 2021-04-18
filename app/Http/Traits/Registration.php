<?php

namespace App\Http\Traits;

use App\Models\User;
use App\Models\Invites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

trait Registration
{
    /**
     * Do the registration of user
     * @param  Request $request
     */
    public function register(Request $request): object
    {
        $fields = [
            'name'      => 'required|string',
            'user_name' => 'required|string|min:4|max:20',
            'email'     => 'required|string|unique:users,email',
            'password'  => 'required|string|confirmed',
            'pin'       => 'required|string',
            'avatar'    => 'mimes:png,jpg|dimensions:min_width=256,min_height=256' # Task-11
        ];

        $isAdmin = false;

        # IF  given by PIN then its a USER role registration
        # otherwise it's an admin.
        if (isset($request->pin)) {
            unset($fields['email']); # Task-17
            $fields = $request->validate($fields);
        } else {
            unset($fields['pin']);
            $fields = $request->validate($fields);
            $isAdmin = true;
        }

        if (isset($request->avatar)) {
            $file = $request->avatar->store('public/avatar');
            $fields['file'] = $file;
        }

        $fields['isAdmin'] = $isAdmin;

        if ($isAdmin === true) {
            $user = self::createUser($fields);
        } else {
            $invite = Invites::where('pin', $request->pin)->first();

            if (!$invite) {
                return response(['message' => 'Sorry we cannot let you proceed.'], 401); #Task-5
            }

            $user = $invite->fresh();
            $fields['email'] = $user->email;
            $user = self::createUser($fields);
            $invite->delete();
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        return response([
            'message' => "Welcome {$request->name} Thank for joining us!",
            'token'   => "Please write down your login token: {$token}",
            'user'    => $user,
        ], 201);
    }

    /**
     * Create new user record
     * @param  array  $fields
     */
    private function createUser(array $fields=[]): object
    {
        return User::create([
            'email'             => $fields['email'],
            'name'              => $fields['name'],
            'user_name'         => $fields['user_name'],
            'password'          => Hash::make($fields['password']),
            'user_role'         => $fields['isAdmin'] === false ? 'user' : 'admin',
            'registered_at'     => date("Y-m-d H:i:s"),
            'email_verified_at' => date("Y-m-d H:i:s"),
            'avatar'            => $fields['file'] ?? ''
        ]);
    }
}
