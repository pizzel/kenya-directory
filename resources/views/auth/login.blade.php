@extends('layouts.site')

@section('title', 'Log In - Discover Kenya')

@section('canonical')
    <link rel="canonical" href="{{ route('login') }}" />
@endsection

@section('content')
<div class="auth-page-wrapper">
    <div class="auth-card">
        
        {{-- Header --}}
        <div class="auth-header">
            <h1>Welcome Back</h1>
            <p>Log in to access your travel passport.</p>
        </div>

        {{-- Alerts --}}
        @if (session('status'))
            <div class="alert-box success">
                {{ session('status') }}
            </div>
        @endif
        
        @if (session('error_419'))
            <div class="alert-box error">
                <i class="fas fa-exclamation-circle"></i> {{ session('error_419') }}
            </div>
        @endif

        {{-- Google Login --}}
        {{-- FIX APPLIED HERE: We pass the 'redirect' parameter to the route --}}
        <a href="{{ route('auth.google.redirect', ['redirect' => request()->get('redirect')]) }}" class="btn-google">
            <img src="{{ asset('images/google-g-logo.svg') }}" alt="G">
            <span>Continue with Google</span>
        </a>

        <div class="auth-divider"><span>or login with email</span></div>

        {{-- Form --}}
        <form id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf
            
            {{-- OPTIONAL FIX: Handle redirection for standard email login too --}}
            @if(request()->has('redirect'))
                <input type="hidden" name="redirect" value="{{ request()->get('redirect') }}">
            @endif

            <!-- Email -->
            <div class="auth-group">
                <label for="email">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@example.com">
                @error('email')
                    <span class="auth-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div class="auth-group">
                <div class="flex-label">
                    <label for="password">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot?</a>
                    @endif
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                @error('password')
                    <span class="auth-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="auth-check-row">
                <label class="custom-checkbox">
                    <input id="remember_me" type="checkbox" name="remember">
                    <span class="checkmark"></span>
                    <span class="label-text">Remember me for 30 days</span>
                </label>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-submit">
                Log In
            </button>

            <!-- Footer -->
            <div class="auth-footer">
                Don't have an account? <a href="{{ route('register') }}">Sign up for free</a>
            </div>
        </form>
    </div>
</div>

<style>
    /* --- PAGE LAYOUT --- */
    .auth-page-wrapper {
        background-color: #f8fafc;
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        font-family: 'Inter', sans-serif;
    }

    .auth-card {
        background: white;
        width: 100%;
        max-width: 440px;
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
    .auth-divider span { background:#fff; padding:0 10px; color: #94a3b8; font-size: 0.85rem; }

    /* --- FORM ELEMENTS --- */
    .auth-group { margin-bottom: 20px; }
    .auth-group label { display: block; font-size: 0.9rem; font-weight: 600; color: #334155; margin-bottom: 6px; }
    
    .auth-group input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.95rem;
        color: #1e293b;
        background: #fff;
        transition: all 0.2s;
        box-sizing: border-box; /* Critical fix for layout width */
    }
    .auth-group input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    
    .auth-error { color: #ef4444; font-size: 0.85rem; margin-top: 5px; display: block; }

    .flex-label { display: flex; justify-content: space-between; align-items: center; }
    .forgot-link { font-size: 0.85rem; color: #3b82f6; text-decoration: none; font-weight: 500; }
    .forgot-link:hover { text-decoration: underline; }

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

    /* --- CHECKBOX --- */
    .auth-check-row { margin-bottom: 25px; }
    .custom-checkbox { display: flex; align-items: center; cursor: pointer; user-select: none; font-size: 0.9rem; color: #475569; }
    .custom-checkbox input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }
    .checkmark { height: 18px; width: 18px; background-color: #fff; border: 1px solid #cbd5e1; border-radius: 4px; margin-right: 10px; position: relative; }
    .custom-checkbox input:checked ~ .checkmark { background-color: #1e293b; border-color: #1e293b; }
    .checkmark:after { content: ""; position: absolute; display: none; }
    .custom-checkbox input:checked ~ .checkmark:after { display: block; }
    .custom-checkbox .checkmark:after { left: 6px; top: 2px; width: 5px; height: 10px; border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg); }

    /* --- FOOTER & ALERTS --- */
    .auth-footer { text-align: center; margin-top: 30px; font-size: 0.9rem; color: #64748b; }
    .auth-footer a { color: #3b82f6; text-decoration: none; font-weight: 600; margin-left: 5px; }
    .auth-footer a:hover { text-decoration: underline; }

    .alert-box { padding: 12px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px; }
    .alert-box.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .alert-box.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(event) {
                if (loginForm.dataset.submitting === 'true') { event.preventDefault(); return; }
                loginForm.dataset.submitting = 'true';

                const submitButton = loginForm.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-circle-notch fa-spin" style="margin-right: 8px;"></i> Accessing Passport...';
                }
            });
        }
    });
</script>
@endsection