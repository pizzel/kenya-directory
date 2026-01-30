<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialiteController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        // DEBUG LOG 1: Start of process
        Log::info('--------------------------------------------------');
        Log::info('GOOGLE AUTH STEP 1: Redirecting to Google');
        Log::info('Request IP: ' . $request->ip());
        Log::info('Full Request URL: ' . $request->fullUrl());
        
        // Check what 'redirect' parameter came in from the Blade file
        $redirectParam = $request->input('redirect');
        Log::info('Incoming "redirect" parameter: ' . ($redirectParam ?? 'NULL'));

        if ($redirectParam) {
            // Store it in the Session
            Session::put('url.intended', $redirectParam);
            Session::save(); // Force save just to be safe

            // Verify it was saved
            Log::info('Saved to Session [url.intended]: ' . Session::get('url.intended'));
        } else {
            Log::warning('No redirect parameter found. User will default to Home.');
        }
        Log::info('Session ID at Step 1: ' . Session::getId());
        Log::info('--------------------------------------------------');

        return Socialite::driver('google')
            ->stateless()
            ->with(['prompt' => 'select_account']) 
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        Log::info('--------------------------------------------------');
        Log::info('GOOGLE AUTH STEP 2: Callback Received');
        Log::info('Session ID at Step 2: ' . Session::getId());
        
        // CHECK: Did the session survive the trip?
        $intendedUrl = Session::get('url.intended');
        Log::info('Retrieved Session [url.intended]: ' . ($intendedUrl ?? 'NULL/MISSING'));

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            Log::info('Google User Retrieved: ' . $googleUser->getEmail());

            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'email_verified_at' => now(),
                ]
            );

            Auth::login($user);
            Log::info('User Logged In. Role: ' . $user->role);

            // ADMIN / OWNER CHECKS
            if ($user->role === 'admin') {
                Log::info('Redirecting Admin to Dashboard');
                return redirect()->to(config('filament.path'))->with('force_reload', true);
            }
            if ($user->role === 'business_owner') {
                Log::info('Redirecting Owner to Dashboard');
                return redirect()->route('business-owner.dashboard')->with('force_reload', true);
            }
            
            // STANDARD USER REDIRECT LOGIC
            if ($intendedUrl) {
                Log::info('SUCCESS: Intended URL found. Redirecting to: ' . $intendedUrl);
                return redirect()->to($intendedUrl)
                    ->with('force_reload', true);
            }

            Log::warning('FAILURE: No Intended URL found. Defaulting to Home.');
            return redirect()->route('home')
                ->with('force_reload', true);

        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR in Google Callback: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Unable to login using Google.');
        }
    }
}