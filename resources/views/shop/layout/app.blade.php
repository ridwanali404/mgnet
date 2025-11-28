<!DOCTYPE html>
<html lang="id-ID">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="description" content="{{ App\Models\Customize::first()->meta_description }}">
    <meta name="keywords" content="{{ App\Models\Customize::first()->meta_keywords }}">
    <meta name="author" content="MG Network" />

    <title>{{ App\Models\Customize::first()->title }} | @yield('title')</title>
    <link rel="icon" href="{{ asset('images/mgnet-favicon.png') }}" type="image/png" />

    <!-- Stylesheets -->
    <link
        href="https://fonts.googleapis.com/css?family=Lato:300,400,400i,700|Montserrat:300,400,500,600,700|Merriweather:300,400,300i,400i&display=swap"
        rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('shop/css/bootstrap.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('shop/style.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('shop/css/dark.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('shop/css/swiper.css') }}" type="text/css" />

    <!-- shop Demo Specific Stylesheet -->
    <link rel="stylesheet" href="{{ asset('shop/demos/shop/shop.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('shop/demos/shop/css/fonts.css') }}" type="text/css" />
    <!-- / -->

    <link rel="stylesheet" href="{{ asset('shop/css/font-icons.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('shop/css/animate.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('shop/css/magnific-popup.css') }}" type="text/css" />

    <link rel="stylesheet" href="{{ asset('shop/css/custom.css') }}" type="text/css" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="stylesheet" href="{{ asset('shop/css/colors5fae.css?color=000000') }}" type="text/css" />

    <!-- Toastr style -->
    <link href="{{ asset('inspinia/css/plugins/toastr/toastr.min.css') }}" rel="stylesheet">

    {{-- save sponsor on local storage --}}
    @if (request()->sponsor)
        <script>
            document.cookie = "sponsor= ; expires = Thu, 01 Jan 1970 00:00:00 GMT"
            localStorage.setItem("sponsor", '{{ request()->sponsor }}');
            //define a function to set cookies
            function setCookie(name, value, days) {
                var expires = "";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
            }

            //get your item from the localStorage
            var sponsor = localStorage.getItem('sponsor');
            setCookie('sponsor', sponsor);
        </script>
    @endif
    <link href="{{ asset('css/shop.css') }}" rel="stylesheet">
    @yield('style')
</head>

<body class="stretched">

    <!-- Document Wrapper -->
    <div id="wrapper" class="clearfix">

        <!-- Login Modal -->
        <div class="modal1 mfp-hide" id="modal-register">
            <div class="card mx-auto" style="max-width: 540px;">
                <div class="card-header py-3 bg-transparent center">
                    <h3 class="mb-0 fw-normal">Halo, Selamat Datang Kembali</h3>
                </div>
                <div class="card-body mx-auto py-5" style="max-width: 70%;">

                    <form id="login-form" name="login-form" class="mb-0 row" method="POST"
                        action="{{ url('login') }}" onsubmit="login.disabled = true;">
                        @csrf
                        <div class="col-12">
                            <input type="text" id="login-form-username" name="username"
                                value="{{ old('username') }}" class="form-control not-dark" placeholder="Username" />
                        </div>

                        <div class="col-12 mt-4">
                            <input type="password" id="login-form-password" name="password" value=""
                                class="form-control not-dark" placeholder="Password" />
                        </div>

                        <div class="col-12">
                            <a href="{{ env('CR_URL') }}/reset-password" class="float-end text-dark fw-light mt-2">Lupa
                                Password?</a>
                        </div>

                        <div class="col-12 mt-4">
                            <button class="button w-100 m-0" id="login-form-submit" name="login"
                                type="submit">Login</button>
                        </div>
                    </form>
                </div>

                @if (Auth::guest())
                    @if (request()->sponsor)
                        <div class="card-footer py-4 center">
                            <p class="mb-0">Tidak punya akun? <a
                                    href="{{ url('register/?sponsor=' . request()->sponsor) }}"><u>Daftar</u></a></p>
                        </div>
                    @elseif(isset($_COOKIE['sponsor']))
                        <div class="card-footer py-4 center">
                            <p class="mb-0">Tidak punya akun? <a
                                    href="{{ url('register/?sponsor=' . $_COOKIE['sponsor']) }}"><u>Daftar</u></a></p>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Top Bar -->
        <div id="top-bar" class="dark" style="background-color: #a3a5a7;">
            <div class="container">

                <div class="row justify-content-between align-items-center">

                    <div class="col-12 col-lg-auto">
                        <p class="mb-0 d-flex justify-content-center justify-content-lg-start py-3 py-lg-0">
                            <strong>Selamat datang di MG Network.</strong>
                        </p>
                    </div>

                    <div class="col-12 col-lg-auto d-none d-lg-flex">

                        <!-- Top Links -->
                        <div class="top-links">
                            <ul class="top-links-container">
                                <li class="top-links-item"><a href="{{ url('about-us') }}">Tentang Kami</a></li>
                                <li class="top-links-item"><a href="{{ url('blog') }}">Blog</a></li>
                            </ul>
                        </div><!-- .top-links end -->

                        <!-- Top Social -->
                        <ul id="top-social">
                            <li><a href="{{ App\Models\ContactUs::first()->facebook }}" class="si-facebook"><span
                                        class="ts-icon"><i class="icon-facebook"></i></span><span
                                        class="ts-text">Facebook</span></a></li>
                            <li><a href="{{ App\Models\ContactUs::first()->instagram }}" class="si-instagram"><span
                                        class="ts-icon"><i class="icon-instagram2"></i></span><span
                                        class="ts-text">Instagram</span></a></li>
                            <li><a href="tel:+62{{ App\Models\ContactUs::first()->phone }}" class="si-call"><span
                                        class="ts-icon"><i class="icon-call"></i></span><span
                                        class="ts-text">+62{{ App\Models\ContactUs::first()->phone }}</span></a></li>
                            <li><a href="mailto:{{ App\Models\ContactUs::first()->email }}" class="si-email3"><span
                                        class="ts-icon"><i class="icon-envelope-alt"></i></span><span
                                        class="ts-text">{{ App\Models\ContactUs::first()->email }}</span></a></li>
                        </ul><!-- #top-social end -->

                    </div>
                </div>

            </div>
        </div>

        <!-- Header -->
        <header id="header" class="full-header header-size-md">
            <div id="header-wrap">
                <div class="container">
                    <div class="header-row justify-content-lg-between">

                        <!-- Logo -->
                        <div id="logo" class="me-lg-4">
                            <a href="{{ url('/') }}" class="standard-logo"><img
                                    src="{{ asset('images/mgnet.webp') }}"
                                    alt="MG Network"></a>
                            <a href="{{ url('/') }}" class="retina-logo"><img
                                    src="{{ asset('images/mgnet.webp') }}"
                                    alt="MG Network"></a>
                        </div><!-- #logo end -->

                        <div class="header-misc">

                            <!-- Top Search -->
                            <div id="top-account">
                                @if (Auth::guest())
                                    <a href="#modal-register" data-lightbox="inline"><i
                                            class="icon-line2-user me-2 position-relative d-none d-sm-inline-block "
                                            style="top: 1px;"></i><span
                                            class="font-primary fw-medium">Login</span></a>
                                @else
                                    <a href="{{ url('account') }}"><i class="icon-line2-user me-2 position-relative"
                                            style="top: 1px;"></i><span
                                            class="d-none d-sm-inline-block font-primary fw-medium">{{ Auth::user()->username }}</span></a>
                                @endif
                            </div><!-- #top-search end -->

                            <!-- Top Cart -->
                            @php
                                if (Auth::user()) {
                                    $carts = Auth::user()
                                        ->carts()
                                        ->whereNull('transaction_id')
                                        ->latest()
                                        ->get();
                                    $carts_count = $carts->count();
                                    $carts_total = 0;
                                    foreach ($carts as $a) {
                                        $carts_total += $a->product->price_used * $a->qty;
                                    }
                                }
                            @endphp
                            <div id="top-cart" class="header-misc-icon d-none d-sm-block">
                                <a href="#" id="top-cart-trigger"><i class="icon-line-bag"></i><span
                                        class="top-cart-number">{{ number_format($carts_count ?? 0, 0, ',', '.') }}</span></a>
                                <div class="top-cart-content">
                                    <div class="top-cart-title">
                                        <h4>Keranjang Belanja</h4>
                                    </div>
                                    <div class="top-cart-items">
                                        @if (Auth::user())
                                            @foreach ($carts as $a)
                                                <div class="top-cart-item">
                                                    <div class="top-cart-item-image">
                                                        <a href="{{ url('product/' . $a->product->dash_name) }}"><img
                                                                src="{{ asset($a->product->image_path) }}"
                                                                alt="{{ $a->product->name }}" /></a>
                                                    </div>
                                                    <div class="top-cart-item-desc">
                                                        <div class="top-cart-item-desc-title">
                                                            <a
                                                                href="{{ url('product/' . $a->product->dash_name) }}">{{ $a->product->name }}</a>
                                                            <span
                                                                class="top-cart-item-price d-block">Rp&nbsp;{{ number_format($a->product->price_used, 0, ',', '.') }}</span>
                                                        </div>
                                                        <div class="top-cart-item-quantity">x
                                                            {{ number_format($a->qty, 0, ',', '.') }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="top-cart-action">
                                        <span
                                            class="top-checkout-price">{{ number_format($carts_total ?? 0, 0, ',', '.') }}</span>
                                        <a href="{{ url('cart') }}" class="button button-3d button-small m-0">Lihat
                                            Keranjang</a>
                                    </div>
                                </div>
                            </div><!-- #top-cart end -->

                            <!-- Top Search -->
                            <div id="top-search" class="header-misc-icon d-none d-sm-block">
                                <a href="#" id="top-search-trigger"><i class="icon-line-search"></i><i
                                        class="icon-line-cross"></i></a>
                            </div><!-- #top-search end -->

                            @if (Auth::user())
                                <div id="top-exit" class="header-misc-icon">
                                    <a href="{{ url('logout') }}"><i class="icon-line-power"></i></a>
                                </div>
                            @endif

                        </div>

                        <div id="primary-menu-trigger">
                            <svg class="svg-trigger" viewBox="0 0 100 100">
                                <path
                                    d="m 30,33 h 40 c 3.722839,0 7.5,3.126468 7.5,8.578427 0,5.451959 -2.727029,8.421573 -7.5,8.421573 h -20">
                                </path>
                                <path d="m 30,50 h 40"></path>
                                <path
                                    d="m 70,67 h -40 c 0,0 -7.5,-0.802118 -7.5,-8.365747 0,-7.563629 7.5,-8.634253 7.5,-8.634253 h 20">
                                </path>
                            </svg>
                        </div>

                        <!-- Primary Navigation -->
                        <nav class="primary-menu with-arrows me-lg-auto">

                            <ul class="menu-container">
                                <li class="menu-item {{ request()->is('/') ? 'current' : '' }}"><a class="menu-link"
                                        href="{{ url('/') }}">
                                        <div>Beranda</div>
                                    </a></li>
                                <li class="menu-item {{ request()->is('product') ? 'current' : '' }}"><a
                                        class="menu-link" href="{{ url('product') }}">
                                        <div>Produk</div>
                                    </a></li>
                                @foreach (App\Models\Page::orderBy('created_at')->get() as $a)
                                    <li @if (Request::url() == url('page/' . $a->dash_name)) class="active" @endif><a
                                            href="{{ url('page/' . $a->dash_name) }}">{{ $a->name }}</a></li>
                                    <li
                                        class="menu-item {{ request()->is('page/' . $a->dash_name) ? 'current' : '' }}">
                                        <a class="menu-link" href="{{ url('page/' . $a->dash_name) }}">
                                            <div>{{ $a->name }}</div>
                                        </a>
                                    </li>
                                @endforeach
                                <li class="menu-item {{ request()->is('blog') ? 'current' : '' }}"><a
                                        class="menu-link" href="{{ url('blog') }}">
                                        <div>Blog</div>
                                    </a></li>
                                <li class="menu-item {{ request()->is('gallery') ? 'current' : '' }}"><a
                                        class="menu-link" href="{{ url('gallery') }}">
                                        <div>Galeri</div>
                                    </a></li>
                                <li class="menu-item {{ request()->is('transaction') ? 'current' : '' }}"><a
                                        class="menu-link" href="{{ url('transaction') }}">
                                        <div>Transaksi</div>
                                    </a></li>
                                @if (Auth::user())
                                    @if (Auth::user()->type == 'member')
                                        <li class="menu-item"><a class="menu-link" href="{{ url('home') }}">
                                                <div>Member Area</div>
                                            </a></li>
                                        @if (Auth::user()->is_stockist || Auth::user()->is_master_stockist)
                                            @if (session('mode') == 'stockist')
                                                <li class="menu-item"><a class="menu-link"
                                                        href="{{ route('auth.member') }}">
                                                        <div>Mode Member</div>
                                                    </a></li>
                                            @else
                                                <li class="menu-item"><a class="menu-link"
                                                        href="{{ route('auth.stockist') }}">
                                                        <div>Mode Stokis</div>
                                                    </a></li>
                                                @if (false)
                                                    <li class="menu-item"><a class="menu-link" href="#stockist"
                                                            data-bs-toggle="modal">
                                                            <div>Mode Stokis</div>
                                                        </a></li>
                                                @endif
                                            @endif
                                        @endif
                                    @endif
                                    @if (Auth::user()->type == 'admin' || Auth::user()->type == 'cradmin' || Auth::user()->is_master_stockist)
                                        <li class="menu-item"><a class="menu-link" href="{{ url('a/dashboard') }}">
                                                <div>Admin Area</div>
                                            </a></li>
                                    @endif
                                @endif
                                @if (Auth::guest())
                                    @if (request()->sponsor)
                                        <li class="menu-item"><a class="menu-link"
                                                href="{{ env('CR_URL') . '/r/' . request()->sponsor }}">
                                                <div>Daftar Sekarang</div>
                                            </a></li>
                                    @elseif(isset($_COOKIE['sponsor']))
                                        <li class="menu-item"><a class="menu-link"
                                                href="{{ env('CR_URL') . '/r/' . $_COOKIE['sponsor'] }}">
                                                <div>Daftar Sekarang</div>
                                            </a></li>
                                    @endif
                                @endif
                            </ul>

                        </nav><!-- #primary-menu end -->

                        <form class="top-search-form"
                            action="{{ request()->is('blog') ? url('blog') : url('product') }}" method="get">
                            <input type="text" name="query" class="form-control"
                                value="{{ request()->get('query') }}" placeholder="Ketik &amp; Tekan Enter.."
                                autocomplete="off">
                        </form>

                    </div>
                </div>
            </div>
            <div class="header-wrap-clone"></div>
        </header><!-- #header end -->

        <!--  -->
        <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-indicators">
                @foreach (App\Models\Banner::orderBy('number')->get() as $a)
                    <button type="button" data-bs-target="#carouselExampleCaptions"
                        data-bs-slide-to="{{ $loop->index }}"
                        {{ $loop->first ? 'class=active aria-current=true' : '' }}
                        aria-label="MG Network"></button>
                @endforeach
            </div>
            <div class="carousel-inner">
                @foreach (App\Models\Banner::orderBy('number')->get() as $a)
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        <img src="{{ url($a->image_path) }}" class="d-block w-100" alt="MG Network"
                            style="{{ $loop->first && (request()->sponsor || isset($_COOKIE['sponsor'])) ? 'filter: brightness(25%)' : '' }}">
                        @if ($loop->first)
                            <div class="carousel-caption">
                                @if (request()->sponsor)
                                    <div class="h5 mb-2 font-secondary" style="font-size: 3vw;">
                                        Presented by
                                        {{ \App\Models\User::where('username', request()->sponsor)->value('name') }}.<br />
                                        Info Hubungi
                                        +62{{ \App\Models\User::where('username', request()->sponsor)->value('phone') }}.
                                    </div>
                                @elseif(isset($_COOKIE['sponsor']))
                                    <div class="h5 mb-2 font-secondary" style="font-size: 3vw;">
                                        Presented by
                                        {{ \App\Models\User::where('username', $_COOKIE['sponsor'])->value('name') }}.<br />
                                        Info Hubungi
                                        +62{{ \App\Models\User::where('username', $_COOKIE['sponsor'])->value('phone') }}.
                                    </div>
                                @else
                                    <div class="h5 mb-2 font-secondary" style="white-space: pre-line">
                                        {{ \App\Models\KeyValue::where('key', 'banner_subtitle')->value('value') }}
                                    </div>
                                @endif
                                <h2 class="bottommargin-sm text-white">
                                    {{ \App\Models\KeyValue::where('key', 'banner_title')->value('value') }}
                                </h2>
                                @if (false)
                                    <a href="{{ url('register') }}"
                                        class="button bg-white text-dark button-light">Daftar
                                        Sekarang</a>
                                @else
                                    @if (request()->sponsor)
                                        <a href="{{ env('CR_URL') . '/r/' . request()->sponsor }}"
                                            class="button bg-white text-dark button-light">Daftar
                                            Sekarang</a>
                                    @elseif(isset($_COOKIE['sponsor']))
                                        <a href="{{ env('CR_URL') . '/r/' . $_COOKIE['sponsor'] }}"
                                            class="button bg-white text-dark button-light">Daftar
                                            Sekarang</a>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        <!-- #Slider End -->

        <!-- Content -->
        <section id="content">
            <div class="content-wrap">
                @yield('content')

                <!-- App Buttons -->
                <div class="section pb-0 mb-0" style="background-color: #f8f5f0">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-6 offset-1 bottommargin-lg d-flex flex-column align-self-center">
                                <h3 class="card-title fw-normal ls0">Coming Soon.<br>BSM Apps on Android PlayStore.
                                </h3>
                                @if (false)
                                    <span>Proactively enable Corporate Benefits.</span>
                                    <div class="mt-3">
                                        <a href="#"
                                            class="button inline-block button-small button-rounded button-desc fw-normal ls1 clearfix"><i
                                                class="icon-apple"></i>
                                            <div><span>Download Canvas Shop</span>App Store</div>
                                        </a>
                                        <a href="#"
                                            class="button inline-block button-small button-rounded button-desc button-light text-dark fw-normal ls1 bg-white border clearfix"><i
                                                class="icon-googleplay"></i>
                                            <div><span>Download Canvas Shop</span>Google Play</div>
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4 d-none d-md-flex align-items-end">
                                <img src="{{ asset('images/hand_cr.png') }}" alt="Image" class="mb-0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last Section -->
                <div class="section footer-stick bg-white m-0 py-3 border-bottom">
                    <div class="container clearfix">

                        <div class="row clearfix">
                            <div class="col-lg-4 col-md-6">
                                <div class="shop-footer-features mb-3 mb-lg-3"><i class="icon-line2-globe-alt"></i>
                                    <h5 class="inline-block mb-0 ms-2 fw-semibold"><a href="#">Trusted
                                            Shipping</a><span class="fw-normal text-muted"> &amp; Easy Returns</span>
                                    </h5>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="shop-footer-features mb-3 mb-lg-3"><i class="icon-line2-notebook"></i>
                                    <h5 class="inline-block mb-0 ms-2"><a href="#">Geniune Products</a><span
                                            class="fw-normal text-muted"> Guaranteed</span></h5>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <div class="shop-footer-features mb-3 mb-lg-3"><i class="icon-line2-lock"></i>
                                    <h5 class="inline-block mb-0 ms-2"><a href="#">256-Bit</a> <span
                                            class="fw-normal text-muted">Secured Checkouts</span></h5>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section><!-- #content end -->

        <!-- Footer -->
        <footer id="footer" class="bg-transparent border-0">

            @if (false)
                <div class="container clearfix">

                    <!-- Footer Widgets -->
                    <div class="footer-widgets-wrap pb-3 border-bottom clearfix">

                        <div class="row">

                            <div class="col-lg-2 col-md-3 col-6">
                                <div class="widget clearfix">

                                    <h4 class="ls0 mb-3 nott">Features</h4>

                                    <ul class="list-unstyled iconlist ms-0">
                                        <li><a href="#">Help Center</a></li>
                                        <li><a href="#">Paid with Moblie</a></li>
                                        <li><a href="#">Status</a></li>
                                        <li><a href="#">Changelog</a></li>
                                        <li><a href="#">Contact Support</a></li>
                                    </ul>

                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-6">
                                <div class="widget clearfix">

                                    <h4 class="ls0 mb-3 nott">Support</h4>

                                    <ul class="list-unstyled iconlist ms-0">
                                        <li><a href="#">Home</a></li>
                                        <li><a href="#">About</a></li>
                                        <li><a href="#">FAQs</a></li>
                                        <li><a href="#">Support</a></li>
                                        <li><a href="#">Contact</a></li>
                                    </ul>

                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-6">
                                <div class="widget clearfix">

                                    <h4 class="ls0 mb-3 nott">Trending</h4>

                                    <ul class="list-unstyled iconlist ms-0">
                                        <li><a href="#">Shop</a></li>
                                        <li><a href="#">Portfolio</a></li>
                                        <li><a href="#">Blog</a></li>
                                        <li><a href="#">Events</a></li>
                                        <li><a href="#">Forums</a></li>
                                    </ul>

                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-6">
                                <div class="widget clearfix">

                                    <h4 class="ls0 mb-3 nott">Get to Know us</h4>

                                    <ul class="list-unstyled iconlist ms-0">
                                        <li><a href="#">Corporate</a></li>
                                        <li><a href="#">Agency</a></li>
                                        <li><a href="#">eCommerce</a></li>
                                        <li><a href="#">Personal</a></li>
                                        <li><a href="#">OnePage</a></li>
                                    </ul>

                                </div>
                            </div>
                            <div class="col-lg-4 col-md-8">
                                <div class="widget clearfix">

                                    <h4 class="ls0 mb-3 nott">Subscribe Now</h4>
                                    <div class="widget subscribe-widget mt-2 clearfix">
                                        <p class="mb-4"><strong>Subscribe</strong> to Our Newsletter to get Important
                                            News, Amazing Offers &amp; Inside Scoops:</p>
                                        <div class="widget-subscribe-form-result"></div>
                                        <form id="widget-subscribe-form"
                                            action="http://themes.semicolonweb.com/html/canvas/include/subscribe.php"
                                            method="post" class="mt-1 mb-0 d-flex">
                                            <input type="email" id="widget-subscribe-form-email"
                                                name="widget-subscribe-form-email"
                                                class="form-control sm-form-control required email"
                                                placeholder="Enter your Email Address">

                                            <button class="button nott fw-normal ms-1 my-0" type="submit">Subscribe
                                                Now</button>
                                        </form>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div><!-- .footer-widgets-wrap end -->

                </div>
            @endif

            <!-- Copyrights -->
            <div id="copyrights" class="bg-transparent">

                <div class="container clearfix">

                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-6">
                            Copyrights &copy; 2025 All Rights Reserved by MG Network<br>
                            <div class="copyright-links"><a href="#">Terms of Use</a> / <a
                                    href="#">Privacy Policy</a></div>
                        </div>

                        <div class="col-md-6 d-md-flex flex-md-column align-items-md-end mt-4 mt-md-0">
                            <div class="copyrights-menu copyright-links clearfix">
                                <a href="#">About</a>/<a href="#">Features</a>/<a
                                    href="#">FAQs</a>/<a href="#">Contact</a>
                            </div>
                        </div>
                    </div>

                </div>

            </div><!-- #copyrights end -->

        </footer><!-- #footer end -->

    </div><!-- #wrapper end -->

    <!-- Go To Top -->
    <div id="gotoTop" class="icon-line-arrow-up"></div>

    @if (false)
        @if (Auth::user() && Auth::user()->type == 'member' && (Auth::user()->is_stockist || Auth::user()->is_master_stockist))
            <form method="POST" action="{{ route('auth.stockist') }}" onsubmit="stockist.disabled = true;">
                @csrf
                <div class="modal fade" id="stockist" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3 class="mb-0 fw-normal">Pin Stokis</h3>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="inputPassword5" class="form-label">Pin</label>
                                    <input type="password" pattern="\d*" id="stockist-form-pin" name="pin"
                                        minlength="4" maxlength="4" class="form-control not-dark"
                                        placeholder="4 DIGIT PIN" required />
                                    <div class="form-text" style="line-height: 1;">
                                        <small>
                                            @if (!Auth::user()->member->member_pin)
                                                Maaf, Anda belum membuat Pin Stokis. Silahkan <a
                                                    href="{{ url('plan-a') }}?redirect={{ env('CR_URL') }}/member/profile">buat
                                                    Pin Stokis</a> terlebih dulu.
                                            @else
                                                Semua transaksi yang dibuat dalam Mode Stokis akan menjadi stok. Harga
                                                produk akan disesuaikan dengan jenis Stokis.
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="button" name="stockist">Masuk Mode Stokis</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    @endif

    <!-- JavaScripts -->
    <script src="{{ asset('shop/js/jquery.js') }}"></script>
    <script src="{{ asset('shop/js/plugins.min.js') }}"></script>

    <!-- Footer Scripts -->
    <script src="{{ asset('shop/js/functions.js') }}"></script>

    <!-- Toastr -->
    <script src="{{ asset('inspinia/js/plugins/toastr/toastr.min.js') }}"></script>

    <!-- ADD-ONS JS FILES -->
    <script>
        $(function() {
            $('form').submit(function() {
                $(this).find('button[type=submit]').prop('disabled', true);
            });
        })
        $(document).ready(function() {
            toastr.options = {
                positionClass: 'toast-bottom-left',
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 4000
            };
            @if (Session::has('fail'))
                toastr.error('{!! Session::pull('fail') !!}', 'Error');
            @elseif (Session::has('success'))
                toastr.success('{!! Session::pull('success') !!}', 'Success');
            @endif
        });
    </script>

    @if (Auth::guest())
        <script>
            $(document).ready(function() {
                var carts = localStorage.getItem('carts');
                if (carts) {
                    var input = {
                        '_token': '{{ csrf_token() }}',
                        'carts': carts
                    };
                    $.post("/cart/check", input, function(data) {
                        if (data) {
                            if (data == '[]') {
                                localStorage.removeItem('carts', data);
                            } else {
                                localStorage.setItem('carts', data);
                                data = JSON.parse(data);
                                $('.top-cart-number').text(data.length);
                                var cartTotal = 0;
                                data.forEach(id => {
                                    $.get("cart/" + id, function(response) {
                                        var cart = JSON.parse(response);
                                        $('.top-cart-items').append(`
										<div class="top-cart-item">
											<div class="top-cart-item-image">
												<a href="/product/` + cart.product.dash_name + `"><img src="` + cart.product.image_path + `" alt="` + cart
                                            .product.name + `" /></a>
											</div>
											<div class="top-cart-item-desc">
												<div class="top-cart-item-desc-title">
													<a href="/product/` + cart.product.dash_name + `">` + cart.product.name + `</a>
													<span class="top-cart-item-price d-block">Rp&nbsp;` + cart.product.price_used.toLocaleString('id-ID', {
                                                currency: 'IDR'
                                            }) + `</span>
												</div>
												<div class="top-cart-item-quantity">x ` + cart.qty.toLocaleString('id-ID') + `</div>
											</div>
										</div>
									`);
                                        var cartTotal = (cart.product.price * cart
                                                .qty) +
                                            parseInt($('.top-checkout-price').text()
                                                .replace(
                                                    /\./g, ''));
                                        $('.top-checkout-price').text(cartTotal
                                            .toLocaleString(
                                                'id-ID'));
                                    });
                                });
                            }
                        }
                    });
                }
            });
        </script>
    @endif

    @yield('script')
</body>

</html>
