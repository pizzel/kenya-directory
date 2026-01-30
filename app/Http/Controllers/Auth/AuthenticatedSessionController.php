<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
        {
            $request->authenticate();
            $user = Auth::user();

            if ($user && $user->blocked_at !== null) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw ValidationException::withMessages([
                    'email' => __('Your account has been suspended. Please contact support.'),
                ]);
            }

            $request->session()->regenerate();

            $intendedUrl = session()->pull('url.intended', null);
            $destination = $intendedUrl ?: route('home');

            if (!$intendedUrl) {
                if ($user->role === 'admin') { $destination = config('filament.path'); }
                if ($user->role === 'business_owner') { $destination = route('business-owner.dashboard'); }
            }
            
            // <<< THE COMBINED, ULTRA-ROBUST SOLUTION >>>
            return redirect()->to($destination)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT')
                ->with('force_reload', true); // Add the JavaScript fallback signal
        }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')->with('force_reload', true);
    }
}