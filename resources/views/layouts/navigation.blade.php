{{-- resources/views/layouts/navigation.blade.php --}}
<nav x-data="{ open: false }" class="bo-navigation">
    <!-- Primary Navigation Menu -->
    <div class="bo-nav-container">
        <div class="bo-nav-flex-wrapper">
            <div class="bo-nav-left">
                <!-- Logo -->
                <div class="bo-nav-logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('images/site-logo.png') }}" alt="{{ config('app.name') }} Logo" class="site-logo-in-nav">
                    </a>
                </div>

                <!-- Desktop Navigation Links - UNCOMMENTED and ADDED My Events -->
                <div class="bo-nav-links">
                    <ul>
                        {{-- Generic Dashboard link for all authenticated users (can be their main landing) --}}
                        <li>
                            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                {{ __('Dashboard') }}
                            </a>
                        </li>

                        @if(Auth::check() && Auth::user()->isBusinessOwner())
                            <li>
                                <a href="{{ route('business-owner.dashboard') }}" class="{{ request()->routeIs('business-owner.dashboard') || request()->routeIs('business-owner.businesses.*') ? 'active' : '' }}">
                                    {{ __('My Businesses') }}
                                </a>
                            </li>
                            <li> {{-- NEW LINK FOR EVENTS (Desktop) --}}
                                <a href="{{ route('business-owner.events.index') }}" class="{{ request()->routeIs('business-owner.events.*') ? 'active' : '' }}">
                                    {{ __('My Events') }}
                                </a>
                            </li>
                        @endif

                        @if(Auth::check() && Auth::user()->isAdmin())
                            <li>
                                <a href="{{ config('filament.path', '/admin') }}" class="{{ request()->is('admin/*') ? 'active' : '' }}">
                                    {{ __('Admin Panel') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="bo-nav-user-dropdown">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="bo-nav-dropdown-trigger">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="dropdown-arrow-icon ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @if(Auth::user()->role === 'user')
                                <x-dropdown-link :href="route('wishlist.index')">
                                    {{ __('My Wishlist') }}
                                </x-dropdown-link>
                            @endif

                            {{-- My Businesses and My Events are now primary nav links for Business Owners,
                                 so they might not need to be repeated in the dropdown unless preferred.
                                 Let's keep them for consistency if the main nav links get hidden on some views/layouts.
                            --}}
                            @if(Auth::user()->isBusinessOwner())
                                <x-dropdown-link :href="route('business-owner.dashboard')">
                                    {{ __('My Businesses Dashboard') }} {{-- Slightly different text for clarity --}}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('business-owner.events.index')">
                                    {{ __('My Events') }}
                                </x-dropdown-link>
                            @endif

                            @if(Auth::user()->isAdmin())
                                <x-dropdown-link href="{{ config('filament.path', '/admin') }}">
                                    {{ __('Admin Panel') }}
                                </x-dropdown-link>
                            @endif

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="bo-nav-hamburger -mr-2 flex items-center sm:hidden"> {{-- Copied classes from Breeze default --}}
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="bo-responsive-nav hidden sm:hidden">
        <div class="bo-responsive-nav-links pt-2 pb-3 space-y-1"> {{-- Added Breeze spacing classes --}}
            <a href="{{ route('dashboard') }}" class="bo-responsive-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                {{ __('Dashboard') }}
            </a>
            @if(Auth::check() && Auth::user()->isBusinessOwner())
                <a href="{{ route('business-owner.dashboard') }}" class="bo-responsive-nav-item {{ request()->routeIs('business-owner.dashboard') || request()->routeIs('business-owner.businesses.*') ? 'active' : '' }}">
                    {{ __('My Businesses') }}
                </a>
                <a href="{{ route('business-owner.events.index') }}" class="bo-responsive-nav-item {{ request()->routeIs('business-owner.events.*') ? 'active' : '' }}"> {{-- NEW LINK (Responsive) --}}
                    {{ __('My Events') }}
                </a>
            @endif
            @if(Auth::check() && Auth::user()->isAdmin())
                <a href="{{ config('filament.path', '/admin') }}" class="bo-responsive-nav-item {{ request()->is('admin/*') ? 'active' : '' }}">
                    {{ __('Admin Panel') }}
                </a>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        @auth
            <div class="bo-responsive-user-options pt-4 pb-1 border-t border-gray-200"> {{-- Added Breeze classes --}}
                <div class="px-4">
                    <div class="font-medium text-base bo-responsive-user-name">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm bo-responsive-user-email">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1"> {{-- Added Breeze classes --}}
                    <a href="{{ route('profile.edit') }}" class="bo-responsive-nav-item"> {{ __('Profile') }} </a>

                    @if(Auth::user()->role === 'user')
                        <a href="{{ route('wishlist.index') }}" class="bo-responsive-nav-item"> {{ __('My Wishlist') }} </a>
                    @endif
                     {{-- My Businesses and My Events already listed above for Business Owners in responsive nav --}}

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" class="bo-responsive-nav-item"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </a>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>