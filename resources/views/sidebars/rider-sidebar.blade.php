<nav id="nav-sidebar">
    <div class="scrollbar">
        <ul>
            <!-- Main Section -->
            <li class="menu-title">
                <span class="flex items-center">
                    <i class="fas fa-th-large mr-2 text-xs"></i>
                    Main Menu
                </span>
            </li>
            <li class="{{ Request::is('driver/dashboard') ? 'active' : '' }}">
                <a href="{{route('rider.dashboard')}}">
                    <i class="fas fa-chart-pie"></i>
                    <span>{{__('messages.dashboard')}}</span>
                </a>
            </li>

            <!-- Deliveries Section -->
            <li class="menu-title">
                <span class="flex items-center">
                    <i class="fas fa-truck mr-2 text-xs"></i>
                    Deliveries
                </span>
            </li>
            <li class="{{ Request::is('driver/my-bookings') ? 'active' : '' }}">
                <a href="{{route('rider.bookings')}}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>{{__('messages.my_bookings')}}</span>
                </a>
            </li>

            <!-- Account Section -->
            <li class="menu-title">
                <span class="flex items-center">
                    <i class="fas fa-user-circle mr-2 text-xs"></i>
                    Account
                </span>
            </li>
            <li class="{{ Request::is('my-profile/'.auth()->user()->id) ? 'active' : '' }}">
                <a href="{{route('settings.profile_view',auth()->user()->id)}}">
                    <i class="fas fa-user-edit"></i>
                    <span>{{__('messages.account_setting')}}</span>
                </a>
            </li>
            <li class="{{ Request::is('change-password/'.auth()->user()->id) ? 'active' : '' }}">
                <a href="{{route('settings.change_password',auth()->user()->id)}}">
                    <i class="fas fa-key"></i>
                    <span>{{__('messages.change_password')}}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form-menu').submit();"
                   class="text-red-400 hover:text-red-300 hover:bg-red-900/20">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>{{__('messages.logout')}}</span>
                </a>
                <form id="logout-form-menu" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</nav>
