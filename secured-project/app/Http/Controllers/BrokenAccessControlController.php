<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrokenAccessControlController extends Controller
{
    public function show(User $user)
{
    
    $user = Auth::user();
    return view('profile', compact('user'));
}

}
