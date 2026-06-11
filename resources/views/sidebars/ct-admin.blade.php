{{-- Cargo Taxi Admin Sidebar --}}

<div class="ct-nav-section">
    <div class="ct-nav-title">Main</div>
    <ul class="ct-nav-menu">
        <li class="ct-nav-item">
            <a href="{{ route('admin.dashboard') }}" class="ct-nav-link {{ Request::is('admin/dashboard') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-chart-pie"></i></span>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="ct-nav-item">
            <a href="{{ route('dispatcher.index') }}" class="ct-nav-link {{ Request::is('dispatcher') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-crosshairs"></i></span>
                <span>Dispatcher</span>
                <span class="ct-nav-badge" style="background: #84cc16; color: #1e293b;">New</span>
            </a>
        </li>
    </ul>
</div>

<div class="ct-nav-section">
    <div class="ct-nav-title">Orders</div>
    <ul class="ct-nav-menu">
        <li class="ct-nav-item">
            <a href="{{ route('bookings.index') }}" class="ct-nav-link {{ Request::is('bookings') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-clipboard-list"></i></span>
                <span>Bookings</span>
            </a>
        </li>
        <li class="ct-nav-item">
            <a href="{{ route('dispatch.logs') }}" class="ct-nav-link {{ Request::is('dispatch-logs') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-history"></i></span>
                <span>Dispatch Logs</span>
            </a>
        </li>
        <li class="ct-nav-item">
            <a href="{{ route('helper.index') }}" class="ct-nav-link {{ request()->routeIs('helper.index') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-hands-helping"></i></span>
                <span>Helper Requests</span>
            </a>
        </li>
        <li class="ct-nav-item">
            <a href="{{ route('map.index') }}" class="ct-nav-link {{ Request::is('live/map') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-map-marker-alt"></i></span>
                <span>Live Tracking</span>
            </a>
        </li>
    </ul>
</div>

<div class="ct-nav-section">
    <div class="ct-nav-title">Analytics</div>
    <ul class="ct-nav-menu">
        <li class="ct-nav-item">
            <a href="{{ route('admin.reports') }}" class="ct-nav-link {{ Request::is('admin/reports') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-chart-line"></i></span>
                <span>Reports</span>
            </a>
        </li>
    </ul>
</div>

<div class="ct-nav-section">
    <div class="ct-nav-title">Users</div>
    <ul class="ct-nav-menu">
        <li class="ct-nav-item">
            <a href="{{ route('users.index', ['type' => 'customer']) }}" class="ct-nav-link {{ Request::is('users') && request('type') === 'customer' ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-user"></i></span>
                <span>Customers</span>
            </a>
        </li>
        <li class="ct-nav-item">
            <a href="{{ route('users.index', ['type' => 'rider']) }}" class="ct-nav-link {{ Request::is('users') && request('type') === 'rider' ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-motorcycle"></i></span>
                <span>Riders</span>
            </a>
        </li>
        <li class="ct-nav-item">
            <a href="{{ route('users.index') }}" class="ct-nav-link {{ Request::is('users') && !request('type') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-users"></i></span>
                <span>All Users</span>
            </a>
        </li>
        <li class="ct-nav-item">
            <a href="{{ route('reviews.index') }}" class="ct-nav-link {{ Request::is('user-reviews') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-star"></i></span>
                <span>Reviews</span>
            </a>
        </li>
    </ul>
</div>

<div class="ct-nav-section">
    <div class="ct-nav-title">Settings</div>
    <ul class="ct-nav-menu">
        <li class="ct-nav-item">
            <a href="{{ route('districts.index') }}" class="ct-nav-link {{ Request::is('districts') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-draw-polygon"></i></span>
                <span>Service Zones</span>
            </a>
        </li>
        <li class="ct-nav-item">
            <a href="{{ route('packages.index') }}" class="ct-nav-link {{ Request::is('packages') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-box"></i></span>
                <span>Packages</span>
            </a>
        </li>
        <li class="ct-nav-item">
            <a href="{{ route('helper.show-fee') }}" class="ct-nav-link {{ Request::is('helper/fee') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-euro-sign"></i></span>
                <span>Helper Fee</span>
            </a>
        </li>
        <li class="ct-nav-item">
            <a href="{{ route('settings.index') }}" class="ct-nav-link {{ Request::is('settings') ? 'active' : '' }}">
                <span class="ct-nav-icon"><i class="fas fa-cog"></i></span>
                <span>Site Settings</span>
            </a>
        </li>
    </ul>
</div>

