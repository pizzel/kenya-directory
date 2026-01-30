import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';

export default function Layout({ children }) {
    const { auth, flash } = usePage().props;
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

    return (
        <div className="min-h-screen bg-slate-50 flex flex-col font-sans">
            {/* Header / Navbar */}
            <header className="site-main-header sticky top-0 z-[1000] bg-white/90 backdrop-blur-md border-b border-black/5 h-[70px]">
                <div className="max-w-[1280px] mx-auto px-5 flex items-center justify-between h-full">
                    {/* Logo */}
                    <div className="logo">
                        <Link href="/">
                            <img src="/images/site-logo.png" alt="Discover Kenya" className="h-10 w-auto" />
                        </Link>
                    </div>

                    {/* Desktop Nav */}
                    <nav className="hidden lg:block desktop-nav">
                        <ul className="flex gap-5 list-none p-0 m-0">
                            <li><Link href="/" className="text-slate-600 font-medium text-[0.95rem] hover:text-blue-600">Home</Link></li>
                            <li><Link href="/listings" className="text-slate-600 font-medium text-[0.95rem] hover:text-blue-600">Explore</Link></li>
                            <li><Link href="/collections" className="text-slate-600 font-medium text-[0.95rem] hover:text-blue-600">Collections</Link></li>
                            <li><Link href="/itineraries" className="text-slate-600 font-medium text-[0.95rem] hover:text-blue-600">Journeys</Link></li>
                            <li><Link href="/blog" className="text-slate-600 font-medium text-[0.95rem] hover:text-blue-600">Blog</Link></li>
                            <li><Link href="/contact-us" className="text-slate-600 font-medium text-[0.95rem] hover:text-blue-600">Contact</Link></li>
                        </ul>
                    </nav>

                    {/* Auth */}
                    <div className="hidden lg:flex items-center gap-3">
                        {auth.user ? (
                            <div className="relative group">
                                <button className="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-md font-medium">
                                    <span>{auth.user.name}</span>
                                    <i className="fas fa-chevron-down text-xs"></i>
                                </button>
                                {/* Dropdown would go here */}
                            </div>
                        ) : (
                            <>
                                <Link href="/login" className="px-4 py-2 text-blue-600 font-medium hover:bg-slate-100 rounded-md transition">Login</Link>
                                <Link href="/register" className="px-4 py-2 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 transition">Sign Up</Link>
                            </>
                        )}
                    </div>

                    {/* Mobile Toggle */}
                    <button
                        className="lg:hidden text-slate-800"
                        onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
                    >
                        <i className="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </header>

            {/* Main Content */}
            <main className="flex-grow">
                {children}
            </main>

            {/* Footer */}
            <footer className="bg-slate-900 text-slate-300 py-20 mt-auto">
                <div className="max-w-[1280px] mx-auto px-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                    <div>
                        <img src="/images/site-logo.png" alt="Logo" className="h-10 opacity-90 mb-5" />
                        <p className="text-sm leading-relaxed">Your ultimate guide to discovering the best businesses, destinations, and experiences in Kenya.</p>
                    </div>
                    {/* Simplified footer links for now */}
                </div>
            </footer>
        </div>
    );
}
