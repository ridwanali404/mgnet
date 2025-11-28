<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ App\Models\Customize::first()->meta_description }}">
    <meta name="keywords" content="{{ App\Models\Customize::first()->meta_keywords }}">
    <meta name="author" content="">

    <title>{{ App\Models\Customize::first()->title }} | @yield('title')</title>
    <link rel="icon" href="{{ asset('images/mgnet-favicon.png') }}" type="image/png" />

    <!-- Bootstrap core CSS -->
    <link href="{{ asset('inspinia/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Toastr style -->
    <link href="{{ asset('inspinia/css/plugins/toastr/toastr.min.css') }}" rel="stylesheet">

    <!-- Animation CSS -->
    <link href="{{ asset('inspinia/css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('inspinia/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">

    <link href="{{ asset('inspinia/css/plugins/iCheck/custom.css') }}" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="{{ asset('inspinia/css/style.css') }}" rel="stylesheet">

    <link href="{{ asset('inspinia/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') }}"
        rel="stylesheet">
    @yield('style')
    <link href="{{ asset('css/inspinia_style.css') }}" rel="stylesheet">

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
</head>

<body id="page-top" class="landing-page no-skin-config">
    <div class="navbar-wrapper">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header page-scroll">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                        aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <!-- <a class="navbar-brand" href="{{ url('/') }}"><span style="font-weight: lighter;">Online </span>SHOP</a> -->
                    <a class="navbar-brand" href="{{ url('/') }}">{{ App\Models\Customize::first()->title }}</a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <li @if (Request::url() == url('/')) class="active" @endif><a
                                href="{{ url('/') }}">Home</a></li>
                        <li @if (Request::url() == url('product')) class="active" @endif><a href="{{ url('product') }}"><i
                                    class="fa fa-cube fa-fw"></i><span class="hidden-sm hidden-md">Product</span></a>
                        </li>
                        @foreach (App\Models\Page::orderBy('created_at')->get() as $a)
                            <li @if (Request::url() == url('page/' . $a->dash_name)) class="active" @endif><a
                                    href="{{ url('page/' . $a->dash_name) }}">{{ $a->name }}</a></li>
                        @endforeach
                        <li @if (Request::url() == url('blog')) class="active" @endif><a href="{{ url('blog') }}"><i
                                    class="fa fa-newspaper-o fa-fw"></i><span
                                    class="hidden-sm hidden-md">Blog</span></a>
                        </li>
                        <li @if (Request::url() == url('gallery')) class="active" @endif><a href="{{ url('gallery') }}"><i
                                    class="fa fa-picture-o fa-fw"></i><span
                                    class="hidden-sm hidden-md">Gallery</span></a>
                        </li>
                        @if (Auth::guest())
                            <li @if (Request::url() == url('about-us')) class="active" @endif><a
                                    href="{{ url('about-us') }}">About Us</a></li>
                        @endif
                        <li @if (Request::url() == url('cart')) class="active" @endif><a href="{{ url('cart') }}"><i
                                    class="fa fa-shopping-cart fa-fw"></i><span
                                    class="hidden-sm hidden-md">Cart</span></a>
                        </li>
                        <li @if (Request::url() == url('transaction')) class="active" @endif><a
                                href="{{ url('transaction') }}"><i class="fa fa-exchange fa-fw"></i><span
                                    class="hidden-sm hidden-md">Transaction</span></a></li>
                        @if (Auth::user())
                            <li @if (Request::url() == url('account')) class="active" @endif><a
                                    href="{{ url('account') }}"><i class="fa fa-user fa-fw"></i></a></li>
                            <li><a href="{{ url('m/home') }}"><i class="fa fa-users fa-fw"></i></a></li>
                            <li><a href="{{ url('logout') }}"><i class="fa fa-power-off fa-fw"></i></a></li>
                        @endif
                        @if (Auth::guest())
                            <li><a data-toggle="modal" href="#modal-form" style="outline: none;">Login/Register</a></li>
                        @endif
                        <!-- <li><a class="page-scroll" href="#page-top">Home</a></li>
                        <li><a class="page-scroll" href="#categories">Category</a></li>
                        <li><a class="page-scroll" href="#about-us">About Us</a></li>
                        <li><a class="page-scroll" href="#products">Product</a></li>
                        <li><a class="page-scroll" href="#testimonials">Testimony</a></li>
                        <li><a class="page-scroll" href="#blogs">Blog</a></li>
                        <li><a class="page-scroll" href="{{ url('gallery') }}">Gallery</a></li>
                        <li><a data-toggle="modal" href="#modal-form" style="outline: none;">Login</a></li> -->
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <div class="carousel slide" id="carousel1">
        <div class="carousel-inner">
            @foreach (App\Models\Banner::orderBy('number')->get() as $a)
                <div class="item @if ($loop->first) active @endif">
                    @if ($loop->first)
                        <div class="container">
                            <div class="carousel-caption blank">
                                <h1>{{ \App\Models\KeyValue::where('key', 'banner_title')->value('value') }}</h1>
                                @if (request()->sponsor)
                                    <p>
                                        Presented by
                                        {{ \App\Models\User::where('username', request()->sponsor)->value('name') }}.<br />
                                        Info Hubungi
                                        +62{{ \App\Models\User::where('username', request()->sponsor)->value('phone') }}.
                                    </p>
                                @elseif(isset($_COOKIE['sponsor']))
                                    <p>
                                        Presented by
                                        {{ \App\Models\User::where('username', $_COOKIE['sponsor'])->value('name') }}.<br />
                                        Info Hubungi
                                        +62{{ \App\Models\User::where('username', $_COOKIE['sponsor'])->value('phone') }}.
                                    </p>
                                @else
                                    <p style="white-space: pre-line">
                                        {{ \App\Models\KeyValue::where('key', 'banner_subtitle')->value('value') }}
                                    </p>
                                @endif
                                <p><a class="btn btn-lg btn-primary" href="{{ url('register') }}"
                                        role="button">Daftar
                                        Sekarang</a></p>
                            </div>
                        </div>
                    @endif
                    <img alt="image" class="img-responsive" src="{{ url($a->image_path) }}"
                        style="height: 470px; width: 100%; object-fit: cover; filter: brightness(50%);">
                </div>
            @endforeach
        </div>
        <a data-slide="prev" href="#carousel1" class="left carousel-control">
            <span class="icon-prev"></span>
        </a>
        <a data-slide="next" href="#carousel1" class="right carousel-control">
            <span class="icon-next"></span>
        </a>
    </div>

    @yield('content')
    <section class="gray-section contact">
        <div class="container">
            <div class="row m-b-lg">
                <div class="col-lg-12 text-center">
                    <div class="navy-line"></div>
                    <h1>Contact Us</h1>
                    <p>Give us a call, send us an email or a letter - or whatsapp to have a chat. We are always here to
                        help out in whatever way we can.</p>
                </div>
            </div>
            <div class="row m-b-lg">
                <div class="col-lg-3 col-lg-offset-3">
                    <address>
                        <strong><span
                                class="navy">{{ App\Models\ContactUs::first()->company }}</span></strong><br />
                        {{ App\Models\ContactUs::first()->address_line_1 }}<br />
                        {{ App\Models\ContactUs::first()->address_line_2 }}<br />
                        <abbr title="Phone">P:</abbr> (+62) {{ App\Models\ContactUs::first()->phone }}
                    </address>
                </div>
                <div class="col-lg-4">
                    <p class="text-color">
                        {{ App\Models\ContactUs::first()->text }}
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 text-center">
                    <a href="mailto:{{ App\Models\ContactUs::first()->email }}" class="btn btn-primary">Send us
                        mail</a>
                    <p class="m-t-sm">
                        Or follow us on social platform
                    </p>
                    <ul class="list-inline social-icon">
                        <li><a target="_blank" href="{{ App\Models\ContactUs::first()->instagram }}"><i
                                    class="fa fa-instagram"></i></a>
                        </li>
                        <li><a target="_blank" href="{{ App\Models\ContactUs::first()->facebook }}"><i
                                    class="fa fa-facebook"></i></a>
                        </li>
                        <li><a target="_blank" href="{{ App\Models\ContactUs::first()->youtube }}"><i
                                    class="fa fa-youtube-play"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2 text-center m-t-lg m-b-lg">
                    <p><strong>&copy; 2020 {{ App\Models\ContactUs::first()->company }}</strong><br />&nbsp;</p>
                </div>
            </div>
        </div>
    </section>
    @if (Auth::guest())
        <div id="modal-form" class="modal fade" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-6 b-r">
                                <h3 class="m-t-none m-b">Sign in</h3>

                                <p>Sign in today for more expirience.</p>

                                <form role="form" method="POST" action="{{ url('login') }}"
                                    onsubmit="login.disabled = true;">
                                    @csrf
                                    <div class="form-group"><label>Username/Email</label> <input type="text"
                                            name="username" placeholder="Enter username or email"
                                            value="{{ old('username') }}" class="form-control"></div>
                                    <div class="form-group"><label>Password</label> <input type="password"
                                            name="password" placeholder="Password" class="form-control"></div>
                                    <div>
                                        <button class="btn btn-sm btn-primary pull-right m-t-n-xs" type="submit"
                                            name="login" style="padding: 5px 10px; font-size: 12px"><strong>Log
                                                in</strong></button>
                                        <label> <input type="checkbox" class="i-checks"> Remember me </label>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-6">
                                <h4>Not a member?</h4>
                                <p>You can create an account:</p>
                                <p class="text-center">
                                    <a href="{{ url('register') }}"><i class="fa fa-sign-in big-icon"
                                            style="font-size: 160px !important;"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Mainly scripts -->
    <script src="{{ asset('inspinia/js/jquery-3.1.1.min.js') }}"></script>
    <script src="{{ asset('inspinia/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('inspinia/js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
    <script src="{{ asset('inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>

    <!-- Custom and plugin javascript -->
    <script src="{{ asset('inspinia/js/inspinia.js') }}"></script>
    <script src="{{ asset('inspinia/js/plugins/pace/pace.min.js') }}"></script>
    <script src="{{ asset('inspinia/js/plugins/wow/wow.min.js') }}"></script>

    <!-- iCheck -->
    <script src="{{ asset('inspinia/js/plugins/iCheck/icheck.min.js') }}"></script>

    <!-- Toastr -->
    <script src="{{ asset('inspinia/js/plugins/toastr/toastr.min.js') }}"></script>

    <script>
        $(function() {
            $('form').submit(function() {
                $(this).find('button[type=submit]').prop('disabled', true);
            });
        });
        $(document).ready(function() {
            toastr.options = {
                positionClass: 'toast-bottom-left',
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 4000
            };
            @if (Session::has('fail'))
                // toastr.error('Silahkan periksa kembali email atau password Anda.', 'Maaf Anda tidak dapat login');
                toastr.error('{!! Session::pull('fail') !!}', 'Error');
            @elseif (Session::has('success'))
                toastr.success('{!! Session::pull('success') !!}', 'Success');
            @endif

            $('body').scrollspy({
                target: '.navbar-fixed-top',
                offset: 80
            });

            // Page scrolling feature
            $('a.page-scroll').bind('click', function(event) {
                var link = $(this);
                $('html, body').stop().animate({
                    scrollTop: $(link.attr('href')).offset().top - 50
                }, 500);
                event.preventDefault();
                $("#navbar").collapse('hide');
            });

            // checkbox
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });
        });
        var cbpAnimatedHeader = (function() {
            var docElem = document.documentElement,
                header = document.querySelector('.navbar-default'),
                didScroll = false,
                changeHeaderOn = 200;

            function init() {
                window.addEventListener('scroll', function(event) {
                    if (!didScroll) {
                        didScroll = true;
                        setTimeout(scrollPage, 250);
                    }
                }, false);
            }

            function scrollPage() {
                var sy = scrollY();
                if (sy >= changeHeaderOn) {
                    $(header).addClass('navbar-scroll')
                } else {
                    $(header).removeClass('navbar-scroll')
                }
                didScroll = false;
            }

            function scrollY() {
                return window.pageYOffset || docElem.scrollTop;
            }
            init();
        })();
        // Activate WOW.js plugin for animation on scrol
        new WOW().init();
    </script>
    @yield('script')
</body>

</html>
