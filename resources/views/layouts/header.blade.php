
<header id="header">
    <div class="top-nav">
        <div class="container">
            <nav id="nav">
                <a href="#" class="nav-opener"><i class="fal fa-close"></i> </a>
                <ul>
<!--                     <li><a href="#">Private Customers</a></li>
                    <li><a href="#">Business Customers</a></li>
                    <li><a href="#">About Crouser X</a></li>
 -->
                     <li class="float-right">
                        <a href="{{ route('login') }}">
                            @if(auth()->user())
                                {{Auth::user()->first_name}} <i class="fas fa-sign-in-alt"></i>
                            @else
                                Login
                            @endif
                        </a>
                    </li>
                    @if(!auth()->user())
                     <li class="float-right">
                        <a href="{{ route('register') }}">
                                Register
                        </a>
                    </li>
                    @endif
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
                    <a href="mailto:support@cargotexi.at" class="email"><i class="fas fa-envelope"></i> support@cargotexi.at</a>
                </li>
                <li>
                    <p>Call us for your questions</p>
                    <a href="tel:00226824489" class="tel"><i class="fas fa-phone"></i> + 00 000 00 00</a>
                </li>
            </ul>
            <!-- <div class="search-box">
                <form class="search-form">
                    <div class="form-group">
                        <input type="search" class="form-control" placeholder="Search...">
                        <button type="submit" class="btn"><i class="fal fa-search"></i></button>
                    </div>
                </form>
            </div> -->
        </div>
    </div>

</header>