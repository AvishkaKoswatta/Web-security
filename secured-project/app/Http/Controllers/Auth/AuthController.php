<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{

    public function showRegisterForm(){
        return view('auth.register');
    }

    public function showLoginForm(){
        return view('auth.login');
    }



    
    
    public function register(Request $request)
    {
        // Limit registration attempts from the same IP
        $ip = $request->ip();
        $maxAttempts = 5; // Allow a maximum of 5 attempts
        $decayMinutes = 15; // Lockout period in minutes
    
        if (RateLimiter::tooManyAttempts('register:' . $ip, $maxAttempts)) {
            return back()->withErrors(['email' => 'Too many registration attempts. Please try again in 15 minutes.']);
        }
    
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'not_regex:/[^A-Za-z\s]/' // Allow only letters and spaces
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                Rule::notIn(['example.com', 'tempmail.com']), // Block specific domains
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'g-recaptcha-response' => 'required|captcha' // reCAPTCHA verification
        ], [
            'email.not_in' => 'Registrations from this domain are not allowed.',
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA to verify you are human.'
        ]);
    
        // Register the user if validation passes
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    
        Auth::login($user);
    
        // Send verification email
        $user->sendEmailVerificationNotification();
    
        // Clear rate limit attempts
        RateLimiter::clear('register:' . $ip);
    
        return redirect()->route('verification.notice');
    }
    

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }
    public function login (Request $request){
        $credentials=$request->validate([
            'email'=>'required|string|email',
            'password'=>'required|string',
        ]);

        if(!Auth::attempt($credentials)){
            return response()->json(['message'=>'Invalid credntials',401]);
        }

        $request->session()->regenerate();

        return response()->json(['message'=>'Logged in successfully.']);
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message'=>'Log out successfully.']);
    }


    
}