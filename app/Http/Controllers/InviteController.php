<?php

namespace App\Http\Controllers;

use App\Mail\InviteCreated;
use App\Mail\Pin;
use App\Models\Invites;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class InviteController extends Controller
{
    /**
     * Process the user acceptance to the invitation
     * @param  string $token
     * @return void
     */
    public function processInvitation(string $token): array
    {
        $invite = Invites::where('token', $token)->first();
        if (!$invite) {
            return ['message' => 'Sorry we cannot let you proceed.'];
        }

        $email = $invite->fresh()->email;
        Mail::to($email)->send(
            new Pin(self::generatePin($invite))
        );

        $message = "Thank you for taking time to accept our invitation. " .
            "We sent a 6 Digit PIN to your email for confirmation, please have it along with your username and password on this link " . route('register');

        return ['message' => $message];
    }

    /**
     * Do a registration
     * @param  Request $request
     */
    public function register(Request $request): array
    {
        $request->validate([
            'name'      => 'required',
            'user_name' => 'required|min:4|max:20',
            'password'  => 'required',
            'pin'       => 'required'
        ]);

        $invite = Invites::where('pin', $request->pin)->first();
        if (!$invite) {
            return ['message' => 'Sorry we cannot let you proceed.']; #Task-5
        }

        $user = $invite->fresh();
        User::create([
            'email'             => $user->email,
            'name'              => $request->name,
            'user_name'         => $request->user_name,
            'password'          => Hash::make($request->password),
            'user_role'         => 'user',
            'registered_at'     => date("Y-m-d H:i:s"),
            'email_verified_at' => date("Y-m-d H:i:s")
        ]);

        $invite->delete();

        return [
            'message' => "Welcome {$request->name} Thank for joining us!"
        ];
    }

    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request): array
    {
        #Task-2
        $request->validate([
            'email' => 'required'
        ]);

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

        $invite = Invites::create([
            'email' => $request->input('email'),
            'token' => $token
        ]);

        Mail::to(
            $request->get('email')
        )->send(
            new InviteCreated($invite)
        );

        return ['message' => 'successfully sent an invitation'];
    }

    /**
     * Generate 6-Random number
     */
    private function generateSixDigit(): int
    {
        $today     = date('YmdHi');
        $startDate = date('YmdHi', strtotime('-10 days'));
        $range     = $today - $startDate;
        $rand1     = rand(0, $range);
        $rand2     = rand(0, 600000);

        return $value = ($rand1 + $rand2);
    }

    /**
     * Generate 6-digit PIN
     */
    public function generatePin(object $invite): int
    {
        pin:
        $pin = self::generateSixDigit();
        if (strlen($pin) != 6) {
            goto pin;
        }

        $invite->update([
            'pin' => $pin
        ]);

        return $pin;
    }
}
