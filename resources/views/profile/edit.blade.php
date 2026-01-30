@extends('layouts.site')

@section('title', 'Account Settings - Discover Kenya')

@section('content')
<div class="settings-page" style="background-color: #f8fafc; min-height: 85vh; padding-bottom: 80px;">

    {{-- 1. PREMIUM HEADER --}}
    <div class="settings-header" style="background: white; border-bottom: 1px solid #e2e8f0; padding: 50px 0 40px;">
        <div class="container">
            <h1 style="font-size: 2.2rem; font-weight: 800; color: #1e293b; margin-bottom: 8px; letter-spacing: -0.02em;">
                Account Settings
            </h1>
            <p style="color: #64748b; font-size: 1.05rem;">
                Manage your profile details, security preferences, and account status.
            </p>
        </div>
    </div>

    <div class="container" style="margin-top: 40px;">
        <div class="settings-grid">
            
            {{-- 2. SIDEBAR NAVIGATION --}}
            <aside class="settings-sidebar">
                <div class="sidebar-menu">
                    <a href="{{ route('dashboard') }}" class="nav-item">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                    <a href="{{ route('profile.edit') }}" class="nav-item active">
                        <i class="fas fa-user-cog"></i> Profile & Security
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="nav-item">
                        <i class="fas fa-heart"></i> My Travel Passport
                    </a>
                    
                    <div class="separator"></div>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-item text-red">
                            <i class="fas fa-sign-out-alt"></i> Log Out
                        </button>
                    </form>
                </div>
            </aside>

            {{-- 3. MAIN CONTENT AREA --}}
            <main class="settings-content">
                
                {{-- CARD 1: PROFILE INFO --}}
                <div class="settings-card">
                    <div class="card-header">
                        <div class="icon-box blue">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h2>Personal Information</h2>
                            <p>Update your display name and email address.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                {{-- CARD 2: PASSWORD --}}
                <div class="settings-card">
                    <div class="card-header">
                        <div class="icon-box orange">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div>
                            <h2>Security</h2>
                            <p>Ensure your account is using a long, random password.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                {{-- CARD 3: DANGER ZONE --}}
                <div class="settings-card danger-zone">
                    <div class="card-header">
                        <div class="icon-box red">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h2 style="color: #ef4444;">Delete Account</h2>
                            <p>Permanently remove your account and all data.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </main>
        </div>
    </div>
</div>

{{-- 
    CSS OVERRIDES 
    This forces the included partials to look modern without editing them individually.
--}}
<style>
    /* Grid Layout */
    .settings-grid {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 40px;
        align-items: start;
    }

    /* Sidebar Navigation */
    .settings-sidebar {
        position: sticky;
        top: 100px;
    }
    .sidebar-menu {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 15px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    }
    .nav-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: #64748b;
        text-decoration: none;
        font-weight: 500;
        border-radius: 10px;
        margin-bottom: 5px;
        transition: all 0.2s;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
        font-size: 0.95rem;
    }
    .nav-item i { width: 25px; text-align: center; margin-right: 10px; font-size: 1.1rem; }
    .nav-item:hover { background-color: #f8fafc; color: #1e293b; }
    .nav-item.active { background-color: #eff6ff; color: #2563eb; font-weight: 600; }
    .nav-item.text-red { color: #ef4444; }
    .nav-item.text-red:hover { background-color: #fef2f2; }
    .separator { border-top: 1px solid #f1f5f9; margin: 10px 0; }

    /* Settings Cards */
    .settings-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        margin-bottom: 30px;
        overflow: hidden;
    }

    .card-header {
        padding: 25px 30px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        gap: 20px;
        align-items: center;
    }
    .icon-box {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .icon-box.blue { background: #eff6ff; color: #2563eb; }
    .icon-box.orange { background: #fff7ed; color: #ea580c; }
    .icon-box.red { background: #fef2f2; color: #ef4444; }

    .card-header h2 { margin: 0 0 4px 0; font-size: 1.1rem; font-weight: 700; color: #1e293b; }
    .card-header p { margin: 0; font-size: 0.9rem; color: #64748b; }

    .card-body { padding: 30px; }

    /* --- FORM OVERRIDES (Fixing the Partials) --- */
    
    /* Hide the default header inside partials since we made our own */
    .card-body header { display: none; }

    /* Label Styling */
    .card-body label {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        color: #334155;
        margin-bottom: 8px;
    }

    /* Input Styling */
    .card-body input[type="text"],
    .card-body input[type="email"],
    .card-body input[type="password"] {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 0.95rem;
        color: #1e293b;
        margin-bottom: 20px;
        transition: border 0.2s, box-shadow 0.2s;
        background-color: #fff;
    }
    .card-body input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    /* Button Styling */
    .card-body button[type="submit"],
    .card-body .inline-flex { /* Targets x-primary-button */
        background-color: #1e293b !important;
        color: white !important;
        padding: 12px 25px !important;
        border-radius: 30px !important;
        font-weight: 600 !important;
        border: none !important;
        cursor: pointer;
        transition: background 0.2s;
        text-transform: none !important;
        font-size: 0.95rem !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .card-body button[type="submit"]:hover,
    .card-body .inline-flex:hover {
        background-color: #334155 !important;
    }

    /* Danger Zone Specifics */
    .danger-zone { border-color: #fecaca; }
    .danger-zone .card-header { background-color: #fef2f2; }
    .danger-zone button { 
        background-color: #ef4444 !important; 
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3) !important;
    }
    .danger-zone button:hover { background-color: #dc2626 !important; }

    /* Mobile Responsiveness */
    @media (max-width: 900px) {
        .settings-grid { grid-template-columns: 1fr; gap: 30px; }
        .settings-sidebar { 
            position: static; 
        }
        .sidebar-menu {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .nav-item { width: auto; flex: 1; justify-content: center; border: 1px solid #e2e8f0; }
        .separator { display: none; }
    }
</style>
@endsection