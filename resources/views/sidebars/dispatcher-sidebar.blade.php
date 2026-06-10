<nav id="nav-sidebar">
    <div class="scrollbar">
        <ul>

            <li class="menu-title">Admin {{__('messages.dashboard')}}</li>
            <li class= "{{ Request::is('admin/dashboard') ? 'active' : '' }}" ><a href="{{route('admin.dashboard')}}"><i class="fal fa-dashboard"></i> {{__('messages.dashboard')}}</a></li>
            <li class="{{ Request::is('dispatcher') ? 'active' : '' }}"><a href="{{route('dispatcher.index')}}"><i class="fal fa-crosshairs"></i>Dispatcher Panel</a></li>

            <li class="{{ Request::is('bookings') ? 'active' : '' }}"><a href="{{route('bookings.index')}}"><i class="fal fa-history"></i>  {{__('messages.bookings')}}</a></li>

            <li class="{!! request()->routeIs('helper.index')?'active':'' !!}">
                <a href="{!! route('helper.index') !!}"><i class="fas fa-male"></i> Helpers </a>
            </li>
            <li class="menu-title">account</li>

            <li class="{{ Request::is('my-profile/'.auth()->user()->id) ? 'active' : '' }}"><a  href="{{route('settings.profile_view',auth()->user()->id)}}"><i class="fal fa-user"></i>{{__('messages.account_setting')}}</a></li>
            <li class="{{ Request::is('change-password/'.auth()->user()->id) ? 'active' : '' }}"><a  href="{{route('settings.change_password',auth()->user()->id)}}"><i class="fal fa-key"></i>{{__('messages.change_password')}}</a></li>
            <li class="{{ Request::is('districts') ? 'active' : '' }}"><a href="{{route('map.index')}}"><i class="fa fa-map-marker" aria-hidden="true"></i>Live Tracking </a></li>
            <li class="menu-title">Reviews</li>
            <li class="{{ Request::is('user-reviews') ? 'active' : '' }}"><a href="{{route('reviews.index')}}">{{__('messages.user_rating')}}</a></li>
            <li class="{{ Request::is('rider-reviews') ? 'active' : '' }}"><a href="{{route('reviews.riders')}}">{{__('messages.rider_rating')}}</a></li>
            <li>
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                                 document.getElementById('logout-form-menu').submit();">
                    <i class="fal fa-sign-out"></i> {{__('messages.logout')}}
                </a>
                <form id="logout-form-menu" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form></li>
        </ul>
    </div>
</nav>
