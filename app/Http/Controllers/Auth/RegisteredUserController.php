<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule; // Import Rule for ENUM validation
use Illuminate\View\View;     // <--- ADD THIS FOR THE RETURN TYPE OF create()

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View // <--- THIS IS THE MISSING METHOD
    {
        return view('auth.register'); // This line loads your resources/views/auth/register.blade.php
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(['user', 'business_owner'])], // Validate the role
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role, // Use the role from the form
            'email_verified_at' => now(), // Auto-verify for dev (as discussed previously)
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Redirect based on role after registration
        if ($user->role === 'business_owner') {
            // Redirect to create business page if they are a business owner
            return redirect(route('business-owner.businesses.create', absolute: false))
                     ->with('status', 'Welcome! Please create your business profile to get started.');
        }
        // Default dashboard for 'user' role or if dashboard route is generic
        return redirect(route('dashboard', absolute: false));
    }
}