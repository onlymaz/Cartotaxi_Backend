{{-- Cargo Taxi Rider/Driver Sidebar --}}

<div class="ct-nav-section">
    <div class="ct-nav-title">Main</div>
    <ul class="ct-nav-menu">
        <li class="ct-nav-item">
            <a href="{{ route('rider.dashboard') }}" class="ct-nav-link {{ Request::is('driver/dashboard') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>Dashboard</span>
            </a>
        </li>
    </ul>
</div>

<div class="ct-nav-section">
    <div class="ct-nav-title">Deliveries</div>
    <ul class="ct-nav-menu">
        <li class="ct-nav-item">
            <a href="{{ route('rider.bookings') }}" class="ct-nav-link {{ Request::is('driver/my-bookings') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-truck"></i></span>
                <span>My Deliveries</span>
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


