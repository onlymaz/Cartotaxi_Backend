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
            <li class="{{ Request::is('admin/dashboard') ? 'active' : '' }}">
                <a href="{{route('admin.dashboard')}}">
                    <i class="fas fa-chart-pie"></i>
                    <span>{{__('messages.dashboard')}}</span>
                </a>
            </li>
            <li class="{{ Request::is('dispatcher') ? 'active' : '' }}">
                <a href="{{route('dispatcher.index')}}">
                    <i class="fas fa-crosshairs"></i>
                    <span>Dispatcher Panel</span>
                </a>
            </li>
            <li class="{{ Request::is('bookings') ? 'active' : '' }}">
                <a href="{{route('bookings.index')}}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>{{__('messages.bookings')}}</span>
                </a>
            </li>
            <li class="{{ request()->routeIs('helper.index') ? 'active' : '' }}">
                <a href="{{ route('helper.index') }}">
                    <i class="fas fa-hands-helping"></i>
                    <span>Helpers</span>
                </a>
            </li>

            <!-- Members Section -->
            <li class="menu-title">
                <span class="flex items-center">
                    <i class="fas fa-users mr-2 text-xs"></i>
                    Members
                </span>
            </li>
            <li class="{{ Request::is('users') ? 'active' : '' }}">
                <a href="{{route('users.index')}}">
                    <i class="fas fa-user-friends"></i>
                    <span>{{__('messages.users')}}</span>
                </a>
            </li>

            <!-- Details Section -->
            <li class="menu-title">
                <span class="flex items-center">
                    <i class="fas fa-info-circle mr-2 text-xs"></i>
                    Details
                </span>
            </li>
            <li class="has-child {{ Request::is('user-reviews') || Request::is('rider-reviews') ? 'has-child-active' : '' }}">
                <a href="#" class="child-toggle">
                    <i class="fas fa-star-half-alt"></i>
                    <span>{{__('messages.rating_and_reviews')}}</span>
                    <i class="fas fa-chevron-down ml-auto text-xs opacity-50"></i>
                </a>
                <ul>
                    <li class="{{ Request::is('user-reviews') ? 'active' : '' }}">
                        <a href="{{route('reviews.index')}}">
                            <i class="fas fa-circle text-xs"></i>
                            <span>{{__('messages.user_rating')}}</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Settings Section -->
            <li class="menu-title">
                <span class="flex items-center">
                    <i class="fas fa-cog mr-2 text-xs"></i>
                    Settings
                </span>
            </li>
            <li class="{{ Request::is('settings') ? 'active' : '' }}">
                <a href="{{route('settings.index')}}">
                    <i class="fas fa-sliders-h"></i>
                    <span>{{__('messages.site_setting')}}</span>
                </a>
            </li>

            <!-- Others Section -->
            <li class="menu-title">
                <span class="flex items-center">
                    <i class="fas fa-ellipsis-h mr-2 text-xs"></i>
                    Others
                </span>
            </li>
            <li class="{{ Request::is('live/map') ? 'active' : '' }}">
                <a href="{{route('map.index')}}">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Live Tracking</span>
                </a>
            </li>
            <li class="{{ Request::is('districts') ? 'active' : '' }}">
                <a href="{{route('districts.index')}}">
                    <i class="fas fa-draw-polygon"></i>
                    <span>Zones</span>
                </a>
            </li>
            <li class="{{ Request::is('packages') ? 'active' : '' }}">
                <a href="{{route('packages.index')}}">
                    <i class="fas fa-box"></i>
                    <span>{{__('messages.package')}}</span>
                </a>
            </li>
            <li class="{{ Request::is('helper/fee') ? 'active' : '' }}">
                <a href="{{route('helper.show-fee')}}">
                    <i class="fas fa-euro-sign"></i>
                    <span>Helper Fee</span>
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
