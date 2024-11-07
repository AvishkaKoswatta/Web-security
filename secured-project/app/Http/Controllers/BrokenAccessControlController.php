<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrokenAccessControlController extends Controller
{
    public function show(User $user)
{
    // Check if the authenticated user is trying to access their own profile
    if ($user->id !== Auth::id()) {
        abort(403, 'Unauthorized action.');
    }

    return view('profile', compact('user'));
}

}
