@extends('layouts.site')

@section('title', 'Contact Us - ' . config('app.name'))
@section('meta_description', 'Get in touch with Discover Kenya. We help you find the best adventures and businesses across the country.')

@section('content')
<div class="contact-page-wrapper">
    
    {{-- 1. PREMIUM HERO HEADER --}}
    <div class="contact-hero">
        <div class="container">
            <span class="badge-pill">Get In Touch</span>
            <h1>We'd love to hear from you</h1>
            <p>Whether you have a question about a listing, need support, or just want to say hello, our team is ready to help.</p>
        </div>
    </div>

    <div class="container" style="margin-top: -40px; position: relative; z-index: 10;">
        <div class="contact-grid">
            
            {{-- COLUMN 1: CONTACT INFO CARDS --}}
            <div class="info-column">
                
                {{-- Card: Email --}}
                <a href="#" class="contact-card contact-email-link" data-user="info" data-domain="discoverkenya.co.ke">
                    <div class="icon-box blue">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h3>Chat with us</h3>
                        <p>Our friendly team is here to help.</p>
                        <span class="link-text email-display"></span>
                    </div>
                </a>

                {{-- Card: Phone --}}
                <a href="#" class="contact-card contact-phone-link" data-country="254" data-number="738681660">
                    <div class="icon-box green">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div>
                        <h3>Call us</h3>
                        <p>Mon-Fri from 8am to 5pm.</p>
                        <span class="link-text phone-display"></span>
                    </div>
                </a>

                {{-- Card: Location --}}
                <div class="contact-card">
                    <div class="icon-box orange">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h3>Visit us</h3>
                        <p>Come say hello at our office HQ.</p>
                        <span class="link-text">127 James Gichuru Rd, Nairobi</span>
                    </div>
                </div>

                {{-- Socials --}}
                <div class="social-connect">
                    <h4>Follow our journey</h4>
                    <div class="social-icons">
                        <a href="#" class="fb"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="tw"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="ig"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="tk"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>

            {{-- COLUMN 2: THE FORM --}}
            <div class="form-column">
                <div class="form-card">
                    <h3>Send us a message</h3>
                    <p class="sub-text">You’ll hear from us within 24 hours.</p>

                    @if(session('success'))
                        <div class="alert success">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                         <div class="alert error">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', auth()->user()?->name) }}" placeholder="Your full name" required>
                            @error('name') <span class="error-msg">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', auth()->user()?->email) }}" placeholder="you@company.com" required>
                            @error('email') <span class="error-msg">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea name="message" id="message" rows="5" maxlength="200" placeholder="Tell us how we can help..." required>{{ old('message') }}</textarea>
                            <div id="messageCharCount" class="char-count">0 / 200</div>
                            @error('message') <span class="error-msg">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>

        </div>

        {{-- 3. MODERN "ABOUT US" GRID --}}
        <div class="mission-section">
            <div class="mission-header">
                <h2>Our Mission</h2>
                <p>We are building the definitive guide to exploring Kenya, connecting adventurers with local businesses.</p>
            </div>
            
            <div class="mission-grid">
                <div class="mission-item">
                    <div class="mission-icon"><i class="fas fa-search-location"></i></div>
                    <h4>Curated Listings</h4>
                    <p>We meticulously list only active, high-quality businesses to ensure you have the best experience.</p>
                </div>
                <div class="mission-item">
                    <div class="mission-icon"><i class="fas fa-images"></i></div>
                    <h4>Visual Discovery</h4>
                    <p>We believe in the power of visuals. Explore destinations through stunning imagery before you go.</p>
                </div>
                <div class="mission-item">
                    <div class="mission-icon"><i class="fas fa-users"></i></div>
                    <h4>User-Focused Experience</h4>
                    <p>Our platform is built for you, the customer looking for things to do. Easily search, discover, and plan your next adventure or leisure activity.</p>
                </div>
                <div class="mission-item">
                    <div class="mission-icon"><i class="fas fa-handshake"></i></div>
                    <h4>Support Local</h4>
                    <p>By connecting you directly with business owners, we help grow the local tourism economy.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /* PAGE LAYOUT */
    .contact-page-wrapper {
        background-color: #f8fafc;
        font-family: 'Inter', sans-serif;
        padding-bottom: 80px;
    }

    /* HERO SECTION */
    .contact-hero {
        background: white;
        padding: 80px 0 100px; /* Extra bottom padding for overlap */
        text-align: center;
        border-bottom: 1px solid #e2e8f0;
    }
    .badge-pill {
        background: #eff6ff;
        color: #3b82f6;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 6px 12px;
        border-radius: 20px;
        letter-spacing: 0.05em;
        display: inline-block;
        margin-bottom: 15px;
    }
    .contact-hero h1 {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 15px;
        letter-spacing: -0.02em;
    }
    .contact-hero p {
        color: #64748b;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* MAIN GRID */
    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 40px;
        align-items: start;
        margin-bottom: 80px;
    }

    /* INFO COLUMN */
    .info-column {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .contact-card {
        background: white;
        padding: 25px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        display: flex;
        gap: 20px;
        align-items: flex-start;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .contact-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        border-color: #cbd5e1;
    }
    .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .icon-box.blue { background: #eff6ff; color: #2563eb; }
    .icon-box.green { background: #f0fdf4; color: #16a34a; }
    .icon-box.orange { background: #fff7ed; color: #ea580c; }

    .contact-card h3 { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
    .contact-card p { font-size: 0.9rem; color: #64748b; margin: 0 0 8px; }
    .link-text { font-weight: 600; color: #3b82f6; font-size: 0.95rem; }

    /* SOCIALS */
    .social-connect { margin-top: 20px; }
    .social-connect h4 { font-size: 0.85rem; text-transform: uppercase; color: #94a3b8; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 15px; }
    .social-icons { display: flex; gap: 10px; }
    .social-icon { width: 40px; height: 40px; background: white; border: 1px solid #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #64748b; transition: all 0.2s; }
    .social-icon:hover { color: white; border-color: transparent; }
    .social-icon.fb:hover { background: #1877f2; }
    .social-icon.tw:hover { background: #1da1f2; }
    .social-icon.ig:hover { background: #e1306c; }
    .social-icon.tk:hover { background: #000000; }

    /* FORM COLUMN */
    .form-card {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
        border: 1px solid #e2e8f0;
    }
    .form-card h3 { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0 0 5px; }
    .sub-text { color: #64748b; margin-bottom: 30px; }

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-weight: 600; font-size: 0.9rem; color: #334155; margin-bottom: 8px; }
    .form-group input, .form-group textarea {
        width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #1e293b; transition: border 0.2s;
        font-family: 'Inter', sans-serif;
    }
    .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .char-count { font-size: 0.75rem; color: #94a3b8; text-align: right; margin-top: 5px; }
    .error-msg { color: #ef4444; font-size: 0.85rem; margin-top: 5px; display: block; }

    .submit-btn {
        width: 100%; background: #1e293b; color: white; padding: 14px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; transition: background 0.2s;
    }
    .submit-btn:hover { background: #0f172a; }

    /* MISSION SECTION */
    .mission-section { text-align: center; }
    .mission-header { margin-bottom: 40px; }
    .mission-header h2 { font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 10px; }
    .mission-header p { color: #64748b; font-size: 1.1rem; }

    .mission-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }
    .mission-item { background: white; padding: 30px; border-radius: 16px; border: 1px solid #e2e8f0; }
    .mission-icon { width: 50px; height: 50px; background: #f0f9ff; color: #0284c7; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin: 0 auto 20px; }
    .mission-item h4 { font-weight: 700; color: #1e293b; margin-bottom: 10px; font-size: 1.1rem; }
    .mission-item p { color: #64748b; font-size: 0.95rem; line-height: 1.6; }

    /* ALERTS */
    .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
    .alert.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .alert.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

    /* MOBILE */
    @media (max-width: 768px) {
        .contact-grid { grid-template-columns: 1fr; gap: 40px; }
        .contact-hero h1 { font-size: 2rem; }
        .form-card { padding: 25px; }
    }
</style>

@endsection

@push('footer-scripts')
<script>
    const msgInput = document.getElementById('message');
    const charCount = document.getElementById('messageCharCount');
    if (msgInput && charCount) {
        msgInput.addEventListener('input', () => {
            charCount.textContent = `${msgInput.value.length} / 200`;
        });
    }
    // --- EMAIL OBFUSCATION ---
    const emailLinks = document.querySelectorAll('.contact-email-link');
    emailLinks.forEach(link => {
        const user = link.getAttribute('data-user');
        const domain = link.getAttribute('data-domain');
        const email = user + '@' + domain;
        
        // Update the display text
        const display = link.querySelector('.email-display');
        if (display) display.textContent = email;
        
        // Update the href on interaction (prevents bot scraping)
        link.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = 'mailto:' + email;
        });
    });

    // --- PHONE OBFUSCATION ---
    const phoneLinks = document.querySelectorAll('.contact-phone-link');
    phoneLinks.forEach(link => {
        const country = link.getAttribute('data-country');
        const number = link.getAttribute('data-number');
        
        // Format for display: +254 738 681 660
        const displayNum = `+${country} ${number.slice(0, 3)} ${number.slice(3, 6)} ${number.slice(6)}`;
        const rawNum = `+${country}${number}`;

        // Update the display text
        const display = link.querySelector('.phone-display');
        if (display) display.textContent = displayNum;
        
        // Update the href on interaction
        link.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = 'tel:' + rawNum;
        });
    });
</script>
@endpush