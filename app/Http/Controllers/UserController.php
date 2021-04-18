<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display the single user given by ID
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return ['message' => 'Sorry, your not or that user is not registered member!'];
        }

        return $user;
    }

    /**
     * Update the specified resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, $id): object
    {
        $request->validate([
            'user_name' => 'min:4|max:20',
            'avatar' => 'mimes:png,jpg|dimensions:min_width=256,min_height=256'
        ]);

        if (auth()->user()->user_role == 'admin') {
            $user = User::find($id);
        } else {
            $user = auth()->user();
        }

        $parameters = self::cleanParameters($request->all());
        extract($parameters);

        if (isset($avatar)) {
            $parameters['avatar'] = $avatar->store('public/avatar');
        }

        $user->update($parameters);

        return response([
            'message' => 'Successfully updated your profile',
            'user'    => $user
        ], 201);
    }

    /**
     * Destroy the user from user table
     * @param  string $id
     */
    public function destroy(int $id): object
    {
        if (auth()->user()->user_role != 'admin') {
            return response([
                'message' => "Sorry, you can't do that!"
            ], 401);
        }

        $userDeleted = User::find($id);
        if (auth()->user()->email == $userDeleted->email) {
            return response([
                'message' => "Are you sure you want to delete your profile?"
            ], 401);
        }

        $name = $userDeleted->name;
        $userDeleted = $userDeleted->delete();

        if (!$userDeleted) {
            return response([
                'message' => "Unsuccessfully deleted {$name} from the records"
            ], 200);
        }

        return response([
            'message' => "Successfully deleted {$name} from the records"
        ], 200);
    }

    /**
     * Admin display all users
     */
    public function index(): object
    {
        if (auth()->user()->user_role != 'admin') {
            return response([
                'message' => "Sorry, you can't do that!"
            ], 401);
        }

        $users = User::get();

        return response([
            'message' => "List all users!",
            'users' => $users
        ], 200);
    }

    /**
     * Remove others that is not needed to be updated
     * @param  array  $parameters
     */
    private function cleanParameters(array $parameters=[]): array
    {
        unset($parameters['email_verified_at']);
        unset($parameters['registered_at']);
        unset($parameters['user_role']);
        unset($parameters['password']);
        unset($parameters['remember_token']);
        unset($parameters['created_at']);
        unset($parameters['updated_at']);

        return $parameters;
    }
}
