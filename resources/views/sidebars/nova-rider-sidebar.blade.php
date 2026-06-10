{{-- Rider/Driver Sidebar Navigation --}}

<!-- Main Section -->
<div class="nova-nav-section">
    <div class="nova-nav-title">Main Menu</div>
    <ul class="nova-nav-list">
        <li class="nova-nav-item">
            <a href="{{ route('rider.dashboard') }}" class="nova-nav-link {{ Request::is('driver/dashboard') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span>{{ __('messages.dashboard') }}</span>
            </a>
        </li>
    </ul>
</div>

<!-- Deliveries Section -->
<div class="nova-nav-section">
    <div class="nova-nav-title">Deliveries</div>
    <ul class="nova-nav-list">
        <li class="nova-nav-item">
            <a href="{{ route('rider.bookings') }}" class="nova-nav-link {{ Request::is('driver/my-bookings') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-truck"></i></span>
                <span>My Deliveries</span>
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
                <span>{{ __('messages.profile') }}</span>
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


