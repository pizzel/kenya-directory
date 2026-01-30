<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Discover Kenya') }} - Business Owner</title>

        <!-- 1. Fonts (Inter) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- 2. Icons (FontAwesome 6) - CRITICAL for the new UI -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- 3. Custom Premium CSS -->
        <link rel="stylesheet" href="{{ asset('css/business_owner_styling.css') }}">
        
        <!-- 4. TomSelect CSS -->
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        
        <style>
            /* TomSelect Tweaks to match theme */
            .ts-wrapper.multi .ts-control > div { 
                background: #eff6ff; 
                color: #1e40af; 
                border: 1px solid #bfdbfe; 
                border-radius: 4px; 
            }
            .ts-control { border-color: #e2e8f0; border-radius: 0.5rem; padding: 10px; }
        </style>
    </head>
    <body class="bo-area-body">
        
        <!-- Navigation -->
        @include('layouts.business_owner_navigation')

        <!-- Page Content -->
        <div class="bo-main-wrapper">
            {{ $slot }}
        </div>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        @stack('scripts')
        @stack('footer-scripts')
    </body>
</html>