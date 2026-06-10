{{-- Cargo Taxi Customer Sidebar --}}

<div class="ct-nav-section">
    <div class="ct-nav-title">Main</div>
    <ul class="ct-nav-menu">
        <li class="ct-nav-item">
            <a href="{{ route('customer.dashboard') }}" class="ct-nav-link {{ Request::is('customer/dashboard') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-home"></i></span>
                <span>Dashboard</span>
            </a>
        </li>
    </ul>
</div>

<div class="ct-nav-section">
    <div class="ct-nav-title">Bookings</div>
    <ul class="ct-nav-menu">
        <li class="ct-nav-item">
            <a href="{{ route('customer.bookings') }}" class="ct-nav-link {{ Request::is('customer/bookings') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-plus-circle"></i></span>
                <span>New Booking</span>
                <span class="ct-nav-badge" style="background: #84cc16; color: #1e293b;">Book Now</span>
            </a>
        </li>
        <li class="ct-nav-item">
            <a href="{{ route('customer.mybookings') }}" class="ct-nav-link {{ Request::is('customer/my-bookings') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-clipboard-list"></i></span>
                <span>My Bookings</span>
            </a>
        </li>
    </ul>
</div>

<div class="ct-nav-section">
    <div class="ct-nav-title">Services</div>
    <ul class="ct-nav-menu">
        <li class="ct-nav-item">
            <a href="{{ route('helper.index') }}" class="ct-nav-link {{ request()->routeIs('helper.index') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-hands-helping"></i></span>
                <span>Request Helper</span>
            </a>
        </li>
    </ul>
</div>

<div class="ct-nav-section">
    <div class="ct-nav-title">Account</div>
    <ul class="ct-nav-menu">
        <li class="ct-nav-item">
            <a href="{{ route('settings.profile_view', auth()->user()->id) }}" class="ct-nav-link {{ Request::is('my-profile/*') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-user"></i></span>
                <span>Profile</span>
            </a>
        </li>
        <li class="ct-nav-item">
            <a href="{{ route('settings.change_password', auth()->user()->id) }}" class="ct-nav-link {{ Request::is('change-password/*') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-key"></i></span>
                <span>Change Password</span>
            </a>
        </li>
    </ul>
</div>

