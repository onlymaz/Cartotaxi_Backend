<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{url('images/favico.ico')}}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?key={!! env('GOOGLE_MAP_API_KEY') !!}&libraries=drawing,places"></script>
    @yield('title')
    <link href="{{ url('css/frontend.css') }}" rel="stylesheet">
    <link href="{{ url('css/main-css.css') }}" rel="stylesheet">
</head>
<body>
<div id="wrapper" @if(empty(auth()->user())) class="login" @endif>
    <header id="header">
        <div class="top-nav">
            <div class="container">
                <nav id="nav">
                    <a href="#" class="nav-opener"><i class="fal fa-close"></i> </a>
                    <ul>
                        <li><a href="#">Private Customers</a></li>
                        <li><a href="#">Business Customers</a></li>
                        <li><a href="#">About Crouser X</a></li>
                        <li><a href="{!! route('login') !!}">login</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="main-header">
            <div class="container">
                <a href="#" class="nav-opener"><i class="fal fa-bars"></i> </a>
                <a href="#" class="contact-opener"><i class="fal fa-phone"></i> </a>
                <a href="#" class="search-opener"><i class="fal fa-search"></i> </a>
                <div class="logo">
                    <a href="index.php">
                        <img src="images/logo.png">
                    </a>
                </div>
                <ul class="contact-list">
                    <li>
                        <p>write us for you questions</p>
                        <a href="mailto:mail@mail.com" class="email"><i class="fas fa-envelope"></i> mail@mail.com</a>
                    </li>
                    <li>
                        <p>Call us for your questions</p>
                        <a href="tel:00000000000" class="tel"><i class="fas fa-phone"></i> + 00 000 00 00</a>
                    </li>
                </ul>
                <div class="search-box">
                    <form class="search-form">
                        <div class="form-group">
                            <input type="search" class="form-control" placeholder="Search...">
                            <button type="submit" class="btn"><i class="fal fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </header>
    @include('frontend.sliders')
    <main id="main">
        @yield('content')
    </main>
    @include('frontend.footer')
</div>
<script type="text/javascript" src="{{url('js/popper.js"')}}"></script>
<script type="text/javascript" src="{{url('js/bootstrap.min.js')}}"></script>
<script type="text/javascript" src="{{url('js/main.js')}}"></script>
<script type="text/javascript" src="{{url('js/slick.js')}}"></script>
@yield('scripts')

</body>
</html>
