<nav class="bo-navigation">
    <div class="bo-nav-container">
        <!-- LEFT: Logo & Links -->
        <div class="bo-nav-left">
            <a href="{{ route('home') }}" class="bo-nav-logo">
                <img src="{{ asset('images/site-logo.png') }}" alt="Discover Kenya">
            </a>

            <!-- Links -->
            <div class="bo-nav-links">
                <a href="{{ route('business-owner.dashboard') }}" class="{{ request()->routeIs('business-owner.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie" style="margin-right: 6px;"></i> Dashboard
                </a>
                
                <a href="{{ route('business-owner.events.index') }}" class="{{ request()->routeIs('business-owner.events.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt" style="margin-right: 6px;"></i> My Events
                </a>
            </div>
        </div>

        <!-- RIGHT: User Menu -->
        <div class="bo-user-menu-container">
            <div class="bo-user-trigger">
                <span>{{ Auth::user()->name }}</span>
                <i class="fas fa-chevron-down" style="margin-left: 8px; font-size: 0.8rem; color: #94a3b8;"></i>
            </div>
            <div class="bo-user-dropdown">
                <a href="{{ route('profile.edit') }}">
                    <i class="fas fa-user-cog" style="width: 20px;"></i> Profile Settings
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">
                        <i class="fas fa-sign-out-alt" style="width: 20px;"></i> Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>