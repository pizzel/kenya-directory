<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // The user is already logged in. Redirect them to their proper "home" based on their role.
                $user = Auth::guard($guard)->user();
                
                // This is the role-based logic that is needed here.
                switch ($user->role) {
                    case 'admin':
                    case 'editor':
                        return redirect(config('filament.path')); // Redirect to Filament admin panel
                    
                    case 'business_owner':
                        return redirect()->route('business-owner.dashboard');

                    case 'user':
                        return redirect()->route('wishlist.index');

                    default:
                        // A safe fallback to the main homepage.
                        return redirect()->route('home');
                }
            }
        }

        // If the user is a guest (not logged in), let them proceed to the requested page (e.g., /login).
        return $next($request);
    }
}