{{-- Customer Sidebar Navigation --}}

<!-- Main Section -->
<div class="nova-nav-section">
    <div class="nova-nav-title">Main Menu</div>
    <ul class="nova-nav-list">
        <li class="nova-nav-item">
            <a href="{{ route('customer.dashboard') }}" class="nova-nav-link {{ Request::is('customer/dashboard') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-home"></i></span>
                <span>{{ __('messages.dashboard') }}</span>
            </a>
        </li>
    </ul>
</div>

<!-- Bookings Section -->
<div class="nova-nav-section">
    <div class="nova-nav-title">Bookings</div>
    <ul class="nova-nav-list">
        <li class="nova-nav-item">
            <a href="{{ route('customer.bookings') }}" class="nova-nav-link {{ Request::is('customer/bookings') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-plus-circle"></i></span>
                <span>New Booking</span>
                <span class="nova-nav-badge" style="background: var(--color-accent);">Book Now</span>
            </a>
        </li>
        <li class="nova-nav-item">
            <a href="{{ route('customer.mybookings') }}" class="nova-nav-link {{ Request::is('customer/my-bookings') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-clipboard-list"></i></span>
                <span>{{ __('messages.my_bookings') }}</span>
            </a>
        </li>
    </ul>
</div>

<!-- Helpers Section -->
<div class="nova-nav-section">
    <div class="nova-nav-title">Helper Service</div>
    <ul class="nova-nav-list">
        <li class="nova-nav-item">
            <a href="{{ route('helper.index') }}" class="nova-nav-link {{ request()->routeIs('helper.index') ? 'active' : '' }}">
                <span class="nova-nav-icon"><i class="fas fa-hands-helping"></i></span>
                <span>Request Helper</span>
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
        <li class="nova-nav-item">
            {{-- Account deletion is destructive — submitted via a CSRF-protected
                 DELETE form, not a GET link, so it cannot be triggered by an
                 image/script src or a click-jacking iframe. --}}
            <form action="{{ route('delete.user', auth()->user()->id) }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');"
                  style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="nova-nav-link" style="background:none;border:0;padding:0;width:100%;text-align:left;cursor:pointer;">
                    <span class="nova-nav-icon"><i class="fas fa-trash-alt"></i></span>
                    <span>Delete Account</span>
                </button>
            </form>
        </li>
    </ul>
</div>


