<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ App\Models\Customize::first()->meta_description }}">
    <meta name="keywords" content="{{ App\Models\Customize::first()->meta_keywords }}">

    <title>{{ App\Models\Customize::first()->title }} | @yield('title')</title>
    <link rel="icon" href="{{ asset(App\Models\Customize::first()->image_path) }}" type="image/png" />

    <link href="{{ asset('inspinia/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('inspinia/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">

    <!-- Toastr style -->
    <link href="{{ asset('inspinia/css/plugins/toastr/toastr.min.css') }}" rel="stylesheet">

    <link href="{{ asset('inspinia/css/animate.css') }}" rel="stylesheet">

    <!-- custom style -->
    @yield('style')

    <link href="{{ asset('inspinia/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/inspinia_style.css') }}" rel="stylesheet">
</head>

<body>

    <div id="wrapper">

        <nav class="navbar-default navbar-static-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav metismenu" id="side-menu">
                    <li class="nav-header">
                        <div class="dropdown profile-element"> <span>
                                <img alt="image" class="img-circle profile-img img-sidebar"
                                    src="{{ asset('img/default_user_image.png') }}" />
                            </span>
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <span class="clear"> <span class="block m-t-xs text-truncate"> <strong
                                            class="font-bold">{{ Auth::user()->email }}</strong>
                                    </span> <span
                                        class="text-muted text-xs block">{{ Auth::user()->type == 'admin' ? 'Administrator' : 'User' }}
                                        <b class="caret"></b></span> </span> </a>
                            <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                <li><a href="{{ url('logout') }}">Logout</a></li>
                            </ul>
                        </div>
                        <div class="logo-element">
                            O<strong>S</strong>
                        </div>
                    </li>
                    <li @if (request()->url() == url('a/dashboard')) class="active" @endif>
                        <a href="{{ url('a/dashboard') }}"><i class="fa fa-th-large"></i> <span
                                class="nav-label">Dashboard</span></a>
                    </li>
                    <li class="{{ request()->segment(2) == 'transaction' ? 'active' : '' }}">
                        <a href="javascript:;"><i class="fa fa-shopping-cart"></i> <span class="nav-label">Transaksi
                            </span><span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level collapse">
                            <li class="{{ request()->segment(3) == 'general' ? 'active' : '' }}"><a
                                    href="{{ route('admin.transaction.general') }}">Transaksi Member</a></li>
                            <li class="{{ request()->segment(3) == 'stockist' ? 'active' : '' }}"><a
                                    href="{{ route('admin.transaction.stockist') }}">Transaksi Stokis</a></li>
                            <li class="{{ request()->segment(3) == 'master' ? 'active' : '' }}"><a
                                    href="{{ route('admin.transaction.master') }}">Transaksi Master Stokis</a></li>
                            <li class="{{ request()->segment(3) == 'official' ? 'active' : '' }}"><a
                                    href="{{ route('admin.transaction.official') }}">Transaksi Perusahaan</a></li>
                        </ul>
                    </li>
                    @if (Auth::user()->type == 'admin' ||
                        (Auth::user()->type == 'cradmin' && in_array('Konten Web', Auth::user()->roles ?? [])))
                        <li @if (strpos(request()->url(), 'a/blog') !== false) class="active" @endif>
                            <a href="{{ url('a/blog') }}"><i class="fa fa-pencil"></i> <span
                                    class="nav-label">Blog</span></a>
                        </li>
                        <li @if (request()->url() == url('a/gallery')) class="active" @endif>
                            <a href="{{ url('a/gallery') }}"><i class="fa fa-picture-o"></i> <span
                                    class="nav-label">Gallery</span></a>
                        </li>
                        <li
                            class="{{ in_array(request()->url(), [
                                url('a/customize'),
                                url('a/category'),
                                url('a/banner'),
                                url('a/about-us'),
                                url('a/contact-us'),
                                url('a/page'),
                                url('a/user'),
                            ])
                                ? 'active'
                                : '' }}">
                            <a href="javascript:;"><i class="fa fa-gear"></i> <span class="nav-label">Settings
                                </span><span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level collapse">
                                <li @if (request()->url() == url('a/customize')) class="active" @endif><a
                                        href="{{ url('a/customize') }}">General</a></li>
                                <li @if (request()->url() == url('a/category')) class="active" @endif><a
                                        href="{{ url('a/category') }}">Category</a></li>
                                <li @if (request()->url() == url('a/banner')) class="active" @endif><a
                                        href="{{ url('a/banner') }}">Banner</a></li>
                                <li @if (request()->url() == url('a/about-us')) class="active" @endif><a
                                        href="{{ url('a/about-us') }}">About Us</a></li>
                                <li @if (request()->url() == url('a/contact-us')) class="active" @endif><a
                                        href="{{ url('a/contact-us') }}">Contact Us</a></li>
                                <li @if (request()->url() == url('a/page')) class="active" @endif><a
                                        href="{{ url('a/page') }}">Page</a></li>
                                {{-- <li @if (request()->url() == url('a/user')) class="active" @endif><a href="{{ url('a/user') }}">Users</a></li> --}}
                            </ul>
                        </li>
                    @endif
                    @if (Auth::user()->type == 'cradmin')
                        @if (in_array('Konten Web', Auth::user()->roles ?? []))
                            <li>
                                <a href="{{ url('a/product') }}"><i class="fa fa-users"></i> <span
                                        class="nav-label">Produk</span></a>
                            </li>
                        @endif
                        @if (in_array('Keuangan', Auth::user()->roles ?? []))
                            <li>
                                <a href="{{ url('monthly') }}"><i class="fa fa-users"></i> <span
                                        class="nav-label">Bonus Bulanan</span></a>
                            </li>
                        @endif
                    @else
                        <li>
                            <a href="{{ url('home') }}"><i class="fa fa-users"></i> <span class="nav-label">Marketing
                                    Panel</span></a>
                        </li>
                    @endif
                </ul>

            </div>
        </nav>

        <div id="page-wrapper" class="gray-bg">
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top  " role="navigation" style="margin-bottom: 0">
                    <div class="navbar-header">
                        <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i
                                class="fa fa-bars"></i> </a>
                    </div>
                    <ul class="nav navbar-top-links navbar-right">
                        <li>
                            <!-- <span class="m-r-sm text-muted welcome-message">Welcome to Online<strong>SHOP</strong>.</span> -->
                            <span class="m-r-sm text-muted welcome-message">Welcome to
                                <strong><a href="{{ url('/') }}"
                                        style="padding: 0;">{{ App\Models\Customize::first()->title }}</a></strong>.</span>
                        </li>
                        <li>
                            <a href="{{ url('logout') }}">
                                <i class="fa fa-sign-out"></i> Log out
                            </a>
                        </li>
                    </ul>

                </nav>
            </div>

            <div class="wrapper wrapper-content">
                @yield('content')
            </div>
            <div class="footer">
                <div>
                    Copyright <strong><a
                            href="{{ url('/') }}">{{ App\Models\Customize::first()->title }}</a></strong> &copy;
                    2021
                </div>
            </div>

        </div>
    </div>

    <!-- Mainly scripts -->
    <script src="{{ asset('inspinia/js/jquery-3.1.1.min.js') }}"></script>
    <script src="{{ asset('inspinia/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('inspinia/js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
    <script src="{{ asset('inspinia/js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>

    <!-- Custom and plugin javascript -->
    <script src="{{ asset('inspinia/js/inspinia.js') }}"></script>
    <script src="{{ asset('inspinia/js/plugins/pace/pace.min.js') }}"></script>

    <!-- Toastr -->
    <script src="{{ asset('inspinia/js/plugins/toastr/toastr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 4000
            };
            @if (Session::has('success'))
                toastr.success('{!! Session::pull('success') !!}', 'Success');
            @elseif (Session::has('fail'))
                toastr.error('{!! Session::pull('fail') !!}', 'Fail');
            @endif
        });
        $(function() {
            $('form').submit(function() {
                $(this).find('button[type=submit]').prop('disabled', true);
            });
        })
    </script>

    <!-- custom script -->
    @yield('script')
</body>

</html>
