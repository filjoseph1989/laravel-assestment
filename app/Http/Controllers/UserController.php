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
            return ['message' => 'Sorry, your not a registered as member!'];
        }

        return $user;
    }

    /**
     * Update the specified resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_name' => 'min:4|max:20',
            'avatar' => 'mimes:png,jpg|dimensions:min_width=256,min_height=256' # Task-12
        ]);

        $user = User::find($id);
        $parameters = self::cleanParameters($request->all());

        extract($parameters);
        $parameters['avatar'] = $avatar->store('public/avatar');
        $user->update($parameters);

        return [
            'message' => 'You are on update',
            'user' => $user
        ];
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
