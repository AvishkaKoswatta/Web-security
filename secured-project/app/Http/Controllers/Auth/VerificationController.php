<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        // Validate the signed URL
        $request->validate([
            'id' => 'required|integer|exists:users,id',
            'hash' => 'required|string',
        ]);

        // Find the user
        $user = User::findOrFail($request->id);

        // Check if the email is already verified
        if (!$user->hasVerifiedEmail()) {
            // Verify the user's email
            $user->markEmailAsVerified();
        }

        // Redirect to the dashboard with a success message
        return redirect('/')->with('verified', 'Email successfully verified!');
    }

    public function resend(Request $request)
    {
        // Validate the request
        $request->validate(['email' => 'required|email|exists:users,email']);

        // Find the user
        $user = User::where('email', $request->email)->first();

        // Send a new verification notification
        $user->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    }
}
