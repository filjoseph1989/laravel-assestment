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
        # Task-13
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
