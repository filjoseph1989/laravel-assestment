<?php

namespace App\Http\Controllers;

use App\Mail\InviteCreated;
use App\Mail\Pin;
use App\Models\Invites;
use App\Models\User;
use App\Http\Traits\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class InviteController extends Controller
{
    use Registration;

    /**
     * Process the user acceptance to the invitation
     * @param  string $token
     */
    public function processInvitation(string $token): object
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
            "We sent a 6 Digit PIN to your email for confirmation, please have it along with your username and password on this link " . route('user.register');

        return response(['message' => $message], 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request): object
    {
        if (auth()->user()->user_role != 'admin') {
            return response([
                'message' => 'You are not allowed to make an invite'
            ], 401);
        }

        $request->validate([ 'email' => 'required' ]);

        # Task-10
        # $request->input('email')

        $invited = Invites::where('email', '=', $request->input('email'))->first();
        if (!is_null($invited)) {
           return ['message' => 'You already invited this person. Would you like to follow up instead?'];
        }

        $invite = Invites::create([
            'email' => $request->input('email'),
            'token' => self::generateToken()
        ]);

        Mail::to(
            $request->get('email')
        )->send(
            new InviteCreated($invite)
        );

        return response(['message' => 'successfully sent an invitation'], 200);
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

    /**
     * Return a token string
     */
    private function generateToken(): string
    {
        token:
        $token = Str::random(10);

        # Here we make sure that we record a unique token
        $invited = Invites::where('token', '=', $token)->first();
        if (!is_null($invited)) {
           goto token;
        }

        return $token;
    }
}
