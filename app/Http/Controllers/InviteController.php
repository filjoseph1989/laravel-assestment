<?php

namespace App\Http\Controllers;

use App\Mail\InviteCreated;
use App\Models\Invites;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class InviteController extends Controller
{
    /**
     * Process the user acceptance to the invitation
     * @param  string $token
     * @return void
     */
    public function accept($token)
    {
        # here we'll look up the user by the token sent provided in the URL
        dump($token);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        # Check here if user is login or setup a middleware
        # validate the incoming request data
        # Check if email exists

        token:
        $token   = Str::random(10);
        $invited = Invites::where('token', '=', $token)->first();
        if (!is_null($invited)) {
           goto token;
        }

        $invited = Invites::where('email', '=', $request->input('email'))->first();
        if (!is_null($invited)) {
           return ['message' => 'You already invited this person. Would you like to follow up instead?'];
        }

        # create a new invite record
        $invite = Invites::create([
            'email' => $request->get('email'),
            'token' => $token
        ]);

        # send the email
        Mail::to(
            $request->get('email')
        )->send(
            new InviteCreated($invite)
        );

        return ['message' => 'successfully sent an invitation'];
    }
}
