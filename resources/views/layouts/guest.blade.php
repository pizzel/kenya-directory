<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
	
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ $title ?? config('app.name', 'Discover Kenya') }} - Authentication</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome CDN (Optional, if your auth pages use icons) -->
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> --}}

    <!-- Authentication Page Specific Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/registration.css') }}">

    {{-- No other global stylesheets like base.css or components.css are linked here
         to keep auth pages distinct, unless registration.css @imports them or
         you specifically want some global styles to apply.
         For a truly distinct look, registration.css should be self-contained or import its own base.
    --}}

    @stack('styles') {{-- For any inline styles pushed from login/register views --}}
</head>
<body class="auth-page"> {{-- Added class for specific body styling --}}
    <div class="auth-container">
        <div class="auth-logo-container">
            <a href="{{ route('home') }}">
                {{-- Replace with your actual logo image --}}
                <img src="{{ asset('images/site-logo.png') }}" alt="{{ config('app.name', 'Discover Kenya') }} Logo">
                {{-- <x-application-logo class="w-auto h-12 fill-current text-gray-700" /> --}}
            </a>
        </div>

        {{-- Optional: Slot for a page title like "Login" or "Register" --}}
        @isset($authCardHeader)
            <div class="auth-card-header">
                <h1>{{ $authCardHeader }}</h1>
            </div>
        @endisset

        {{ $slot }} {{-- This is where login.blade.php or register.blade.php content goes --}}
    </div>

    {{-- No global script.js linked here unless specifically needed for auth forms --}}
    @stack('footer-scripts')
</body>
</html>