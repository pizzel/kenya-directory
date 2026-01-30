import React, { useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import SearchBar from '../Components/SearchBar';

export default function Layout({ children }) {
    const { auth, flash, ziggy } = usePage().props;
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const [isUserDropdownOpen, setIsUserDropdownOpen] = useState(false);

    // Sync mobile menu state with body class for styling (to match Blade's logic if needed)
    useEffect(() => {
        if (isMobileMenuOpen) {
            document.body.classList.add('mobile-menu-active');
        } else {
            document.body.classList.remove('mobile-menu-active');
        }
    }, [isMobileMenuOpen]);

    const toggleMobileMenu = () => setIsMobileMenuOpen(!isMobileMenuOpen);

    return (
        <div className="min-h-screen bg-slate-50 flex flex-col font-sans">
            {/* 1. OVERLAY BACKDROP */}
            <div
                id="mobileBackdrop"
                className={`mobile-nav-overlay ${isMobileMenuOpen ? 'active' : ''}`}
                onClick={toggleMobileMenu}
                style={{ display: isMobileMenuOpen ? 'block' : 'none' }}
            ></div>

            {/* Header / Navbar */}
            <header className="site-main-header">
                <div className="container header-container">
                    {/* Logo */}
                    <div className="logo">
                        <Link href={route('home')}>
                            <img src="/images/site-logo.png" alt="Discover Kenya" className="site-logo-image" width="116" height="40" />
                        </Link>
                    </div>

                    {/* Desktop Nav */}
                    {/* Desktop nav */}
                    <nav className="desktop-nav">
                        <ul>
                            <li>
                                <Link href={route('home')} className={route().current('home') ? 'active' : ''}>
                                    <i className="fas fa-home" style={{ marginRight: '5px' }}></i> Home
                                </Link>
                            </li>
                            <li>
                                <Link href={route('listings.index')} className={ziggy?.location?.includes('/listings') || ziggy?.location?.includes('/listing') ? 'active' : ''}>
                                    <i className="fas fa-compass" style={{ marginRight: '5px' }}></i> Explore
                                </Link>
                            </li>
                            <li>
                                <Link href={route('collections.index')} className={ziggy?.location?.includes('/collections') ? 'active' : ''}>
                                    <i className="fas fa-layer-group" style={{ marginRight: '5px' }}></i> Collections
                                </Link>
                            </li>
                            <li>
                                <Link href={route('itineraries.index')} className={ziggy?.location?.includes('/itineraries') ? 'active' : ''}>
                                    <i className="fas fa-route" style={{ marginRight: '5px' }}></i> Journeys
                                </Link>
                            </li>
                            <li>
                                <Link href={route('posts.index')} className={ziggy?.location?.includes('/blog') ? 'active' : ''}>
                                    <i className="fas fa-blog" style={{ marginRight: '5px' }}></i> Blog
                                </Link>
                            </li>
                            <li>
                                <Link href={route('contact.show')} className={route().current('contact.show') ? 'active' : ''}>
                                    <i className="fas fa-envelope" style={{ marginRight: '5px' }}></i> Contact
                                </Link>
                            </li>
                        </ul>
                    </nav>


                    {/* Desktop Auth */}
                    <div className="auth-buttons desktop-auth">
                        {auth.user ? (
                            <div className="user-dropdown-wrapper" onClick={() => setIsUserDropdownOpen(!isUserDropdownOpen)}>
                                <button className={`user-dropdown-trigger login-btn ${isUserDropdownOpen ? 'active' : ''}`}>
                                    <span>{auth.user.name}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" style={{ marginLeft: '8px' }}><path d="m6 9 6 6 6-6" /></svg>
                                </button>

                                {isUserDropdownOpen && (
                                    <div className="user-dropdown-menu" style={{ display: 'block' }}>
                                        {auth.user.role === 'admin' ? (
                                            <Link href={route('admin.dashboard')} className="dropdown-item">Admin Panel</Link>
                                        ) : auth.user.role === 'business_owner' ? (
                                            <Link href={route('business-owner.dashboard')} className="dropdown-item">Business Dashboard</Link>
                                        ) : (
                                            <Link href={route('wishlist.index')} className="dropdown-item">My Bucketlist</Link>
                                        )}
                                        <Link href={route('profile.edit')} className="dropdown-item">Profile Settings</Link>
                                        <div style={{ borderTop: '1px solid #f1f5f9', margin: '5px 0' }}></div>
                                        <Link href={route('logout')} method="post" as="button" className="dropdown-item" style={{ width: '100%', textAlign: 'left', border: 'none', background: 'none', cursor: 'pointer' }}>
                                            Logout
                                        </Link>
                                    </div>
                                )}
                            </div>
                        ) : (
                            <>
                                <Link href={route('login')} className="login-btn">Login</Link>
                                <Link href={route('register')} className="signup-btn">Sign Up</Link>
                            </>
                        )}
                    </div>

                    {/* Mobile Toggle */}
                    <div className="mobile-menu-toggle">
                        <button onClick={toggleMobileMenu} aria-label="Open mobile navigation menu">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1e293b" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                        </button>
                    </div>
                </div>
            </header>

            {/* 2. MOBILE SIDEBAR DRAWER */}
            <div id="mobileNavPanel" className={`mobile-nav-panel ${isMobileMenuOpen ? 'is-open' : ''}`} style={{ pointerEvents: 'auto' }}>
                <div className="mobile-nav-header">
                    <Link href={route('home')}>
                        <img src="/images/site-logo.png" alt="Discover Kenya" className="site-logo-image" width="87" height="30" />
                    </Link>
                    <button onClick={toggleMobileMenu} className="mobile-nav-close">×</button>
                </div>

                <div className="mobile-nav-content">
                    <Link href={route('home')} className="mobile-nav-item" onClick={toggleMobileMenu}>Home</Link>
                    <Link href={route('listings.index')} className="mobile-nav-item" onClick={toggleMobileMenu}>Explore</Link>
                    <Link href={route('collections.index')} className="mobile-nav-item" onClick={toggleMobileMenu}>Collections</Link>
                    <Link href={route('posts.index')} className="mobile-nav-item" onClick={toggleMobileMenu}>Travel Blog</Link>
                    <Link href={route('contact.show')} className="mobile-nav-item" onClick={toggleMobileMenu}>Contact Us</Link>
                </div>

                <div className="mobile-nav-footer">
                    {auth.user ? (
                        <>
                            <div style={{ marginBottom: '15px', fontWeight: 600, color: '#1e293b', display: 'flex', alignItems: 'center' }}>
                                <div style={{ width: '35px', height: '35px', background: '#e2e8f0', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: '10px' }}>{auth.user.name.substring(0, 1)}</div>
                                {auth.user.name}
                            </div>
                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px' }}>
                                <Link href={route('wishlist.index')} className="mobile-btn secondary" style={{ fontSize: '0.8rem' }} onClick={toggleMobileMenu}>Bucketlist</Link>
                                <Link href={route('logout')} method="post" as="button" className="mobile-btn secondary" style={{ borderColor: '#fecaca', color: '#ef4444', fontSize: '0.8rem' }} onClick={toggleMobileMenu}>Logout</Link>
                            </div>
                            {(auth.user.role === 'business_owner' || auth.user.role === 'admin') && (
                                <Link href={route('business-owner.dashboard')} className="mobile-btn primary" style={{ marginTop: '10px' }} onClick={toggleMobileMenu}>Dashboard</Link>
                            )}
                        </>
                    ) : (
                        <>
                            <Link href={route('login')} className="mobile-btn secondary" onClick={toggleMobileMenu}>Log In</Link>
                            <Link href={route('register')} className="mobile-btn primary" onClick={toggleMobileMenu}>Create Account</Link>
                        </>
                    )}
                </div>
            </div>

            {/* Global Search Bar */}
            <SearchBar />

            {/* Main Content */}
            <main className="site-main-content">
                {children}
            </main>

            {/* Footer */}
            <footer className="site-footer">
                <div className="container">
                    <div className="footer-grid">
                        <div className="footer-brand">
                            <img src="/images/site-logo.png" alt="Discover Kenya" className="footer-logo" width="116" height="40" />
                            <p>Your ultimate guide to discovering the best businesses, destinations, and experiences in Kenya.</p>
                            <div className="footer-socials">
                                <a href="#" aria-label="Facebook"><i className="fab fa-facebook-f"></i></a>
                                <a href="#" aria-label="Twitter"><i className="fab fa-twitter"></i></a>
                                <a href="#" aria-label="Instagram"><i className="fab fa-instagram"></i></a>
                                <a href="#" aria-label="TikTok"><i className="fab fa-tiktok"></i></a>
                            </div>
                        </div>

                        <div className="footer-links">
                            <p className="footer-heading">Discover</p>
                            <ul>
                                <li><Link href={route('listings.index')}>Browse Listings</Link></li>
                                <li><Link href={route('collections.index')}>Collections</Link></li>
                                <li><Link href={route('posts.index')}>Travel Blog</Link></li>
                            </ul>
                        </div>

                        <div className="footer-links">
                            <p className="footer-heading">Company</p>
                            <ul>
                                <li><Link href={route('contact.show')}>About Us</Link></li>
                                <li><Link href={route('contact.show')}>Contact Support</Link></li>
                                <li><Link href={route('contact.show')}>Privacy Policy</Link></li>
                            </ul>
                        </div>

                        <div className="footer-newsletter">
                            <p className="footer-heading">Join the Adventure</p>
                            <p>Get the latest hidden gems delivered to your inbox.</p>
                            <div className="newsletter-form-wrapper">
                                <input type="email" placeholder="Enter your email" required />
                                <button>Subscribe</button>
                            </div>
                        </div>
                    </div>

                    <div className="footer-bottom">
                        <p>&copy; {new Date().getFullYear()} KenyaDirectory. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    );
}
