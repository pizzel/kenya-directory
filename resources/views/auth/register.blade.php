@extends('layouts.site')

@section('title', 'Create Account - Discover Kenya')
@section('canonical')
    <link rel="canonical" href="{{ route('register') }}" />
@endsection

@section('content')
<div class="auth-page-wrapper">
    <div class="auth-card">
        
        {{-- Header --}}
        <div class="auth-header">
            <h1>Create Your Passport</h1>
            <p>Join the community to save places, track visits, and leave reviews.</p>
        </div>

        {{-- Google Signup --}}
        {{-- FIX APPLIED: Pass the redirect parameter to the route --}}
        <a href="{{ route('auth.google.redirect', ['redirect' => request()->get('redirect')]) }}" class="btn-google">
            <img src="{{ asset('images/google-g-logo.svg') }}" alt="G">
            <span>Sign up with Google</span>
        </a>

        <div class="auth-divider"><span>or register with email</span></div>

        {{-- Form --}}
        <form id="registerForm" method="POST" action="{{ route('register') }}">
            @csrf
            
            {{-- OPTIONAL: Keep the redirect alive if they register manually --}}
            @if(request()->has('redirect'))
                <input type="hidden" name="redirect" value="{{ request()->get('redirect') }}">
            @endif

            <!-- Name -->
            <div class="auth-group">
                <label for="name">Full Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="e.g. Daniel Ng'ang'a">
                @error('name')
                    <span class="auth-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="auth-group">
                <label for="email">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="name@example.com">
                @error('email')
                    <span class="auth-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Role Selection (Styled Dropdown) -->
            <div class="auth-group">
                <label for="role">I want to...</label>
                <div class="custom-select-wrapper">
                    <select id="role" name="role" required class="form-select">
                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Explore & Review (Traveler)</option>
                        <option value="business_owner" {{ old('role') == 'business_owner' ? 'selected' : '' }}>List My Business (Owner)</option>
                    </select>
                    <i class="fas fa-chevron-down select-arrow"></i>
                </div>
                @error('role')
                    <span class="auth-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div class="auth-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 characters">
                @error('password')
                    <span class="auth-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="auth-group">
                <label for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat password">
                @error('password_confirmation')
                    <span class="auth-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-submit">
                Create Account
            </button>

            <!-- Footer -->
            <div class="auth-footer">
                {{-- FIX APPLIED: If they switch back to login, keep the redirect param --}}
                Already have an account? <a href="{{ route('login', ['redirect' => request()->get('redirect')]) }}">Log in</a>
            </div>
        </form>
    </div>
</div>

<style>
    /* --- PAGE LAYOUT (Shared with Login for consistency) --- */
    .auth-page-wrapper {
        background-color: #f8fafc;
        min-height: 85vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        font-family: 'Inter', sans-serif;
    }

    .auth-card {
        background: white;
        width: 100%;
        max-width: 480px; /* Slightly wider than login for extra fields */
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
    }

    /* --- TYPOGRAPHY --- */
    .auth-header { text-align: center; margin-bottom: 30px; }
    .auth-header h1 { font-size: 1.75rem; font-weight: 800; color: #1e293b; margin: 0 0 8px 0; letter-spacing: -0.02em; }
    .auth-header p { color: #64748b; font-size: 0.95rem; margin: 0; }

    /* --- GOOGLE BUTTON --- */
    .btn-google {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        background: white;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 12px;
        font-weight: 600;
        color: #334155;
        text-decoration: none;
        transition: all 0.2s;
        gap: 10px;
        margin-bottom: 25px;
    }
    .btn-google:hover { background-color: #f8fafc; border-color: #94a3b8; color: #0f172a; }
    .btn-google img { width: 20px; height: 20px; }

    /* --- DIVIDER --- */
    .auth-divider {
        text-align: center; border-bottom: 1px solid #e2e8f0; line-height: 0.1em; margin: 10px 0 30px;
    }
    .auth-divider span { background:#fff; padding:0 10px; color: #94a3b8; font-size: 0.85rem; font-weight: 500; text-transform: uppercase; }

    /* --- FORM ELEMENTS --- */
    .auth-group { margin-bottom: 20px; }
    .auth-group label { display: block; font-size: 0.9rem; font-weight: 600; color: #334155; margin-bottom: 6px; }
    
    .auth-group input, 
    .auth-group select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.95rem;
        color: #1e293b;
        background: #fff;
        transition: all 0.2s;
        box-sizing: border-box;
        appearance: none; /* Removes default arrow for select */
    }
    .auth-group input:focus, 
    .auth-group select:focus { 
        outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); 
    }
    
    .auth-error { color: #ef4444; font-size: 0.85rem; margin-top: 5px; display: block; }

    /* Custom Select Wrapper */
    .custom-select-wrapper { position: relative; }
    .select-arrow {
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);
        color: #64748b;
        font-size: 0.8rem;
        pointer-events: none;
    }

    /* --- SUBMIT BUTTON --- */
    .btn-submit {
        width: 100%;
        background-color: #1e293b;
        color: white;
        padding: 14px;
        border-radius: 30px;
        font-weight: 700;
        font-size: 1rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-submit:hover { background-color: #0f172a; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .btn-submit:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

    /* --- FOOTER --- */
    .auth-footer { text-align: center; margin-top: 30px; font-size: 0.9rem; color: #64748b; }
    .auth-footer a { color: #3b82f6; text-decoration: none; font-weight: 600; margin-left: 5px; }
    .auth-footer a:hover { text-decoration: underline; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function(event) {
                if (registerForm.dataset.submitting === 'true') { event.preventDefault(); return; }
                registerForm.dataset.submitting = 'true';

                const submitButton = registerForm.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-circle-notch fa-spin" style="margin-right: 8px;"></i> Creating Account...';
                }
            });
        }
    });
</script>
@endsection