{{-- Admin Sidebar Navigation --}}

<!-- Main Section -->
<div class="nova-nav-section">
    <div class="nova-nav-title">Main Menu</div>
    <ul class="nova-nav-list">
        <li class="nova-nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nova-nav-link {{ Request::is('admin/dashboard') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-chart-pie"></i></span>
                <span>{{ __('messages.dashboard') }}</span>
            </a>
        </li>
        <li class="nova-nav-item">
            <a href="{{ route('dispatcher.index') }}" class="nova-nav-link {{ Request::is('dispatcher') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-crosshairs"></i></span>
                <span>Dispatcher</span>
                <span class="nova-nav-badge" style="background: #84cc16; color: #1e293b;">New</span>
            </a>
        </li>
    </ul>
</div>

<!-- Orders Section -->
<div class="nova-nav-section">
    <div class="nova-nav-title">Orders & Bookings</div>
    <ul class="nova-nav-list">
        <li class="nova-nav-item">
            <a href="{{ route('bookings.index') }}" class="nova-nav-link {{ Request::is('bookings') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-clipboard-list"></i></span>
                <span>{{ __('messages.bookings') }}</span>
            </a>
        </li>
        <li class="nova-nav-item">
            <a href="{{ route('helper.index') }}" class="nova-nav-link {{ request()->routeIs('helper.index') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-hands-helping"></i></span>
                <span>Helper Requests</span>
            </a>
        </li>
        <li class="nova-nav-item">
            <a href="{{ route('map.index') }}" class="nova-nav-link {{ Request::is('live/map') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-map-marked-alt"></i></span>
                <span>Live Tracking</span>
            </a>
        </li>
    </ul>
</div>

<!-- Members Section -->
<div class="nova-nav-section">
    <div class="nova-nav-title">Members</div>
    <ul class="nova-nav-list">
        <li class="nova-nav-item">
            <a href="{{ route('users.index') }}" class="nova-nav-link {{ Request::is('users') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-users"></i></span>
                <span>{{ __('messages.users') }}</span>
            </a>
        </li>
        <li class="nova-nav-item has-submenu {{ Request::is('user-reviews') || Request::is('rider-reviews') ? 'open' : '' }}">
            <a href="#" class="nova-nav-link">
                <span class="nova-nav-icon"><i class="fas fa-star"></i></span>
                <span>{{ __('messages.rating_and_reviews') }}</span>
                <i class="fas fa-chevron-down nova-nav-toggle"></i>
            </a>
            <ul class="nova-nav-submenu">
                <li class="nova-nav-item">
                    <a href="{{ route('reviews.index') }}" class="nova-nav-link {{ Request::is('user-reviews') ? 'active' : '' }}">
                        <span class="nova-nav-icon"><i class="fas fa-circle" style="font-size: 6px;"></i></span>
                        <span>{{ __('messages.user_rating') }}</span>
                    </a>
                </li>
                <li class="nova-nav-item">
                    <a href="{{ route('reviews.riders') }}" class="nova-nav-link {{ Request::is('rider-reviews') ? 'active' : '' }}">
                        <span class="nova-nav-icon"><i class="fas fa-circle" style="font-size: 6px;"></i></span>
                        <span>Driver Reviews</span>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</div>

<!-- Configuration Section -->
<div class="nova-nav-section">
    <div class="nova-nav-title">Configuration</div>
    <ul class="nova-nav-list">
        <li class="nova-nav-item">
            <a href="{{ route('districts.index') }}" class="nova-nav-link {{ Request::is('districts') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-draw-polygon"></i></span>
                <span>Service Zones</span>
            </a>
        </li>
        <li class="nova-nav-item">
            <a href="{{ route('packages.index') }}" class="nova-nav-link {{ Request::is('packages') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-box"></i></span>
                <span>{{ __('messages.package') }}</span>
            </a>
        </li>
        <li class="nova-nav-item">
            <a href="{{ route('helper.show-fee') }}" class="nova-nav-link {{ Request::is('helper/fee') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-euro-sign"></i></span>
                <span>Helper Fee</span>
            </a>
        </li>
        <li class="nova-nav-item">
            <a href="{{ route('settings.index') }}" class="nova-nav-link {{ Request::is('settings') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-cog"></i></span>
                <span>{{ __('messages.site_setting') }}</span>
            </a>
        </li>
    </ul>
</div>

<!-- Account Section -->
<div class="nova-nav-section">
    <div class="nova-nav-title">Account</div>
    <ul class="nova-nav-list">
        <li class="nova-nav-item">
            <a href="{{ route('settings.profile_view', auth()->user()->id) }}" class="nova-nav-link {{ Request::is('my-profile/'.auth()->user()->id) ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-user-circle"></i></span>
                <span>{{ __('messages.account_setting') }}</span>
            </a>
        </li>
        <li class="nova-nav-item">
            <a href="{{ route('settings.change_password', auth()->user()->id) }}" class="nova-nav-link {{ Request::is('change-password/'.auth()->user()->id) ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-key"></i></span>
                <span>{{ __('messages.change_password') }}</span>
            </a>
        </li>
    </ul>
</div>

