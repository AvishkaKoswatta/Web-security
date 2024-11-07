<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\TrustedDevice;
use App\Models\TwoFaQuestion;
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
        // Validate registration form including 2FA question and answer
        $validatedData = $request->validate([
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
            'two_fa_question' => 'required|string',
            'two_fa_answer' => 'required|string|min:3', // Set a minimum length for the answer
            'g-recaptcha-response' => 'required|captcha' // reCAPTCHA verification
        ], [
            'email.not_in' => 'Registrations from this domain are not allowed.',
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA to verify you are human.'
        ]);   
        // Register the user if validation passes
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);    
        // Create the 2FA security question entry for the user
        TwoFaQuestion::create([
            'user_id' => $user->id,
            'question' => $validatedData['two_fa_question'],
            'answer' => Hash::make($validatedData['two_fa_answer']),
        ]);
    
        Auth::login($user); 
        // Send verification email
        $user->sendEmailVerificationNotification();
        // Clear rate limit attempts
        RateLimiter::clear('register:' . $ip);
    
        return redirect()->route('verification.notice');
    }
    
    public function login (Request $request){
        $credentials=$request->validate([
            'email'=>'required|string|email',
            'password'=>'required|string',
        ]);
        $ip = $request->ip();
        $maxAttempts = 5; // Allow a maximum of 5 attempts
        $decayMinutes = 15;
        if (RateLimiter::tooManyAttempts('login:' . $request->ip(), $maxAttempts)) {
            return back()->withErrors(['email' => 'Too many login attempts. Please try again later.']);
        }
        
        if(!Auth::attempt($credentials)){
            return response()->json(['message'=>'Invalid credntials',401]);
        }
        $request->session()->regenerate();
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $deviceIdentifier = $this->getDeviceIdentifier($request);
    
            // Check if the device is already trusted
            if (!$this->isDeviceTrusted($user, $deviceIdentifier)) {
                // Trigger 2FA if the device is not trusted
                
                //$user->notify(new NewDeviceLoginNotification());
                return redirect()->route('2fa.verify');
            }
    
            // Otherwise, allow login and remember the device as trusted
            $this->storeTrustedDevice($user, $deviceIdentifier);
            $request->session()->regenerate();
            return redirect()->intended('/');
            //return response()->json(['message'=>'Logged in successfully.']);
        }
            return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);        
    }


    protected function getDeviceIdentifier(Request $request)
{
    // This can be an IP, user-agent, or other identifier
    return hash('sha256', $request->ip() . $request->userAgent());
}

protected function isDeviceTrusted($user, $deviceIdentifier)
{
    return TrustedDevice::where('user_id', $user->id)
                        ->where('device_identifier', $deviceIdentifier)
                        ->exists();
}

protected function storeTrustedDevice($user, $deviceIdentifier)
{
    TrustedDevice::create([
        'user_id' => $user->id,
        'device_identifier' => $deviceIdentifier,
    ]);
}

public function show2faVerify()
{
    return view('auth.2fa_verify');
}

public function verify2fa(Request $request)
{
    $user = Auth::user();
    $twoFaQuestion = TwoFaQuestion::where('user_id', $user->id)->first();

    if ($twoFaQuestion && Hash::check($request->two_fa_answer, $twoFaQuestion->answer)) {
        // Store this device as trusted
        $this->storeTrustedDevice($user, $this->getDeviceIdentifier($request));
        return redirect()->intended('dashboard');
    }
    return back()->withErrors(['two_fa_answer' => 'Incorrect answer. Please try again.']);
}


    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message'=>'Log out successfully.']);
    }   
}