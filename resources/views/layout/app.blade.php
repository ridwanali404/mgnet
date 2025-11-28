<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/mgnet-favicon.png') }}">
    <title>MG Network | @yield('title')</title>
    <!-- Bootstrap Core CSS -->
    <link href="{{ asset('material-pro/assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- toast CSS -->
    <link href="{{ asset('material-pro/assets/plugins/toast-master/css/jquery.toast.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('material-pro/material/css/style.css') }}" rel="stylesheet">
    <!-- You can change the theme colors from here -->
    <link href="{{ asset('material-pro/material/css/colors/cr.css') }}" id="theme" rel="stylesheet">
    @yield('style')
    <!-- own style -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        .user-profile {
            background: url("{{ asset('material-pro/assets/images/background/user-info.jpg') }}") no-repeat;
        }
    </style>
</head>

<body class="fix-header fix-sidebar card-no-border">
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2"
                stroke-miterlimit="10" />
        </svg>
    </div>
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        <header class="topbar">
            <nav class="navbar top-navbar navbar-expand-md navbar-light">
                <!-- ============================================================== -->
                <!-- Logo -->
                <!-- ============================================================== -->
                <div class="navbar-header">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <!-- Logo icon -->
                        <b>
                            <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                            <!-- Dark Logo icon -->
                            <img height="40" widht="40" src="{{ asset('images/mgnet.webp') }}"
                                alt="homepage" class="dark-logo" />
                            <!-- Light Logo icon -->
                            <img height="40" widht="40" src="{{ asset('images/mgnet.webp') }}" alt="homepage"
                                class="light-logo" />
                        </b>
                        <!--End Logo icon -->
                        <!-- Logo text -->
                        <span>
                            <!-- dark Logo text -->
                            <span class="dark-logo" style="font-size: 18px; font-weight: 600; color: #333; margin-left: 10px;">MG Network</span>
                            <!-- Light Logo text -->
                            <span class="light-logo d-none" style="font-size: 18px; font-weight: 600; color: #fff; margin-left: 10px;">MG Network</span>
                        </span>
                    </a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav mr-auto mt-md-0">
                        <!-- This is  -->
                        <li class="nav-item"> <a
                                class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark"
                                href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
                        <li class="nav-item"> <a
                                class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark"
                                href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
                        @if (auth()->user()->type != 'admin')
                            <li class="nav-item">
                                <span class="nav-link text-{{ auth()->user()->color() }}">
                                    {{ auth()->user()->userPin->pin->name_short ?? 'Free Member' }}
                                    <i class="mdi mdi-crown text-{{ auth()->user()->color() }}"></i>
                                </span>
                            </li>
                        @endif
                    </ul>
                    <!-- ============================================================== -->
                    <!-- User profile and search -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav my-lg-0">
                        <!-- ============================================================== -->
                        <!-- Profile -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href=""
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img
                                    src="{{ asset('images/user.png') }}" alt="user" class="profile-pic" /></a>
                            <div class="dropdown-menu dropdown-menu-right scale-up">
                                <ul class="dropdown-user">
                                    <li>
                                        <div class="dw-user-box">
                                            <div class="u-img"><img src="{{ asset('images/user.png') }}"
                                                    alt="user">
                                            </div>
                                            <div class="u-text" style="width: calc(100% - 90px)">
                                                <h4 class="text-truncate">
                                                    {{ auth()->user()->username }}
                                                    <i class="mdi mdi-crown text-{{ auth()->user()->color() }}"></i>
                                                </h4>
                                                @if (auth()->user()->type == 'admin' || auth()->user()->type == 'member')
                                                    <p class="text-muted text-truncate">{{ auth()->user()->email }}
                                                    </p>
                                                    <a href="{{ url('user/' . auth()->id() . '/profile') }}"
                                                        class="btn btn-rounded btn-danger btn-sm">View Profile</a>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                    <li role="separator" class="divider"></li>
                                    <li><a href="{{ url('logout') }}"><i class="mdi mdi-power"></i> Keluar</a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <aside class="left-sidebar d-print-none">
            <!-- Sidebar scroll-->
            <div class="scroll-sidebar">
                @if (auth()->user()->type == 'admin')
                    <!-- User profile -->
                    <div class="user-profile">
                        <!-- User profile image -->
                        <div class="profile-img"> <img src="{{ asset('images/user.png') }}" alt="user" /> </div>
                        <!-- User profile text-->
                        <div class="profile-text">
                            <a href="#" class="dropdown-toggle u-dropdown" data-toggle="dropdown"
                                role="button" aria-haspopup="true" aria-expanded="true">
                                @if (auth()->user()->premiumUserPin)
                                    <i class="mdi mdi-crown text-warning"></i>
                                @endif
                                {{ auth()->user()->username }}
                            </a>
                            <div class="dropdown-menu animated flipInY">
                                <a href="{{ url('user/' . auth()->id() . '/profile') }}" class="dropdown-item"><i
                                        class="mdi mdi-account-settings"></i> Profil</a>
                                <div class="dropdown-divider"></div>
                                <a href="{{ url('logout') }}" class="dropdown-item"><i class="mdi mdi-power"></i>
                                    Keluar</a>
                            </div>
                        </div>
                    </div>
                    <!-- End User profile text-->
                @endif
                <!-- Sidebar navigation-->
                <nav class="sidebar-nav">
                    <ul id="sidebarnav">
                        @if (in_array(auth()->user()->type, ['admin', 'member']))
                            <li class="{{ request()->segment(1) == 'home' ? 'active' : '' }}">
                                <a href="{{ url('home') }}" aria-expanded="false"><i
                                        class="mdi mdi-gauge"></i><span class="hide-menu">Dashboard</span></a>
                            <li>
                            <li class="{{ request()->is('userPin') ? 'active' : '' }}">
                                <a href="{{ route('userPin.index') }}" aria-expanded="false"><i
                                        class="mdi mdi-cards"></i><span class="hide-menu">Pin</span></a>
                            </li>
                            <li class="{{ Route::is('user.create') ? 'active' : '' }}">
                                <a href="{{ route('user.create') }}" aria-expanded="false"><i
                                        class="mdi mdi-account-plus"></i><span class="hide-menu">Registrasi</span></a>
                            </li>
                            <li class="{{ request()->segment(1) == 'referral' ? 'active' : '' }}">
                                <a href="{{ url('referral') }}" aria-expanded="false"><i
                                        class="mdi mdi-account-network"></i><span
                                        class="hide-menu">Referral</span></a>
                            </li>
                            <li class="{{ request()->segment(1) == 'monoleg' ? 'active' : '' }}">
                                <a href="{{ url('monoleg') }}" aria-expanded="false"><i
                                        class="mdi mdi-account-settings"></i><span
                                        class="hide-menu">Monoleg</span></a>
                            </li>
                            <li class="{{ request()->segment(1) == 'tree' ? 'active' : '' }}">
                                <a href="{{ url('tree') }}" aria-expanded="false"><i
                                        class="mdi mdi-network"></i><span class="hide-menu">Tree</span></a>
                            </li>
                            <li class="{{ Route::is('automaintain.index') ? 'active' : '' }}">
                                <a href="{{ route('automaintain.index') }}" aria-expanded="false"><i
                                        class="mdi mdi-autorenew"></i><span class="hide-menu">Automaintain</span></a>
                            </li>
                        @endif
                        @if (auth()->user()->type == 'admin')
                            <li class="{{ request()->segment(1) == 'users' ? 'active' : '' }}">
                                <a href="{{ url('users') }}" aria-expanded="false"><i
                                        class="mdi mdi-account-multiple"></i><span class="hide-menu">Semua
                                        Member</span></a>
                            </li>
                            <li class="{{ request()->segment(1) == 'stockist' ? 'active' : '' }}">
                                <a href="{{ url('stockist') }}" aria-expanded="false"><i
                                        class="mdi mdi-account-star"></i><span class="hide-menu">Stokis</span></a>
                            </li>
                            <li class="{{ request()->segment(1) == 'stockist-area' ? 'active' : '' }}">
                                <a href="{{ url('stockist-area') }}" aria-expanded="false"><i
                                        class="mdi mdi-map-marker-multiple"></i><span class="hide-menu">Area Master
                                        Stokis</span></a>
                            </li>
                            <li class="{{ request()->segment(1) == 'news' ? 'active' : '' }}">
                                <a href="{{ url('news') }}" aria-expanded="false"><i
                                        class="mdi mdi-newspaper"></i><span class="hide-menu">Berita Member</span></a>
                            </li>
                        @endif
                        @if (in_array(auth()->user()->type, ['admin', 'member']))
                            <li class="{{ request()->segment(1) == 'official-transaction' ? 'active' : '' }}">
                                <a href="{{ url('official-transaction') }}" aria-expanded="false"><i
                                        class="mdi mdi-swap-horizontal"></i><span class="hide-menu">Transaksi Produk
                                        RO</span></a>
                            </li>
                        @endif
                        @if (auth()->user()->type == 'member')
                            <li class="{{ request()->segment(1) == 'monthly' ? 'active' : '' }}">
                                <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                                    <i class="mdi mdi-gift"></i><span class="hide-menu">Bonus</span>
                                </a>
                                <ul aria-expanded="false" class="collapse">
                                    <li><a class="{{ request()->segment(1) == 'daily2' ? 'active' : '' }}"
                                            href="{{ url('daily2') }}">Bonus Harian</a></li>
                                    <li><a class="{{ in_array(request()->segment(1), ['daily', 'weekly']) ? 'active' : '' }}"
                                            href="{{ url('daily') }}">Bonus Mingguan</a></li>
                                    <li><a class="{{ request()->segment(1) == 'monthly' ? 'active' : '' }}"
                                            href="{{ url('monthly') }}">Bonus Bulanan</a></li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->type == 'admin')
                            <li class="{{ request()->segment(1) == 'daily2' ? 'active' : '' }}">
                                <a href="{{ url('daily2') }}" aria-expanded="false"><i
                                        class="mdi mdi-gift"></i><span class="hide-menu">Bonus Harian</span></a>
                            </li>
                            <li class="{{ in_array(request()->segment(1), ['daily', 'weekly']) ? 'active' : '' }}">
                                <a href="{{ url('daily') }}" aria-expanded="false"><i
                                        class="mdi mdi-gift"></i><span class="hide-menu">Bonus Mingguan</span></a>
                            </li>
                            <li class="{{ request()->segment(1) == 'monthly' ? 'active' : '' }}">
                                <a href="{{ url('monthly') }}" aria-expanded="false"><i
                                        class="mdi mdi-gift"></i><span class="hide-menu">Bonus Bulanan</span></a>
                            </li>
                        @endif
                        @if (auth()->user()->type == 'admin' || (auth()->user()->type == 'member' && auth()->user()->userPin?->level >= 3))
                            <li class="{{ Route::is('userAward.index') ? 'active' : '' }}">
                                <a href="{{ route('userAward.index') }}" aria-expanded="false"><i
                                        class="mdi mdi-trophy"></i><span class="hide-menu">Reward</span></a>
                            </li>
                            <li class="{{ Route::is('userRank.index') ? 'active' : '' }}">
                                <a href="{{ route('userRank.index') }}" aria-expanded="false"><i
                                        class="mdi mdi-star"></i><span class="hide-menu">Peringkat</span></a>
                            </li>
                        @endif
                        @if (auth()->user()->type == 'admin')
                            <li class="nav-devider"></li>
                            <li class="nav-small-cap">BELANJA ONLINE</li>
                            <li class="{{ request()->segment(1) == 'product' ? 'active' : '' }}">
                                <a href="{{ route('product.index') }}" aria-expanded="false"><i
                                        class="mdi mdi-package"></i><span class="hide-menu">Produk</span></a>
                            </li>
                            <li>
                                <a href="{{ url('a/dashboard') }}" aria-expanded="false"><i
                                        class="mdi mdi-store"></i><span class="hide-menu">Belanja Online
                                        Panel</span></a>
                            </li>
                        @elseif(auth()->user()->type == 'member')
                            <li>
                                <a href="{{ url('/') }}" aria-expanded="false"><i
                                        class="mdi mdi-store"></i><span class="hide-menu">Belanja Online</span></a>
                            </li>
                            <li class="{{ request()->segment(3) == 'profile' ? 'active' : '' }}">
                                <a href="{{ url('user/' . auth()->id() . '/profile') }}" aria-expanded="false"><i
                                        class="mdi mdi-account-settings"></i><span class="hide-menu">Pengaturan
                                        Profile</span></a>
                            </li>
                        @endif
                        @if (auth()->user()->type == 'admin')
                            <li class="nav-devider"></li>
                            <li class="nav-small-cap">KONFIGURASI</li>
                            <li class="{{ request()->route()->getName() == 'config.daily'? 'active': '' }}">
                                <a href="{{ route('config.daily') }}" aria-expanded="false"><i
                                        class="mdi mdi-settings"></i><span class="hide-menu">Bonus Harian</span></a>
                            </li>
                            <li class="{{ request()->route()->getName() == 'config.monthly'? 'active': '' }}">
                                <a href="{{ route('config.monthly') }}" aria-expanded="false"><i
                                        class="mdi mdi-settings"></i><span class="hide-menu">Bonus Bulanan</span></a>
                            </li>
                            <li class="{{ request()->segment(1) == 'pin' ? 'active' : '' }}">
                                <a href="{{ url('pin') }}" aria-expanded="false"><i
                                        class="mdi mdi-cards-outline"></i><span class="hide-menu">Pin</span></a>
                            </li>
                            <li class="{{ Route::is('award.index') ? 'active' : '' }}">
                                <a href="{{ route('award.index') }}" aria-expanded="false"><i
                                        class="mdi mdi-trophy"></i><span class="hide-menu">Reward</span></a>
                            </li>
                            <li class="{{ Route::is('rank.index') ? 'active' : '' }}">
                                <a href="{{ route('rank.index') }}" aria-expanded="false"><i
                                        class="mdi mdi-star"></i><span class="hide-menu">Peringkat</span></a>
                            </li>
                            <li class="nav-devider"></li>
                            <li class="nav-small-cap">AUTH</li>
                            <li class="{{ request()->segment(3) == 'profile' ? 'active' : '' }}">
                                <a href="{{ url('user/' . auth()->id() . '/profile') }}" aria-expanded="false"><i
                                        class="mdi mdi-account"></i><span class="hide-menu">Profil</span></a>
                            </li>
                            <li class="{{ request()->segment(1) == 'admin' ? 'active' : '' }}">
                                <a href="{{ url('admin') }}" aria-expanded="false"><i class="mdi mdi-key"></i><span
                                        class="hide-menu">Admin CR</span></a>
                            </li>
                        @endif
                        {{-- admin CR --}}
                        @if (auth()->user()->type == 'cradmin')
                            @if (in_array('Konten Web', auth()->user()->roles ?? []))
                                <li class="{{ request()->segment(1) == 'product' ? 'active' : '' }}">
                                    <a href="{{ route('product.index') }}" aria-expanded="false"><i
                                            class="mdi mdi-package"></i><span class="hide-menu">Produk</span></a>
                                </li>
                            @endif
                            @if (in_array('Keuangan', auth()->user()->roles ?? []))
                                <li class="{{ request()->segment(1) == 'daily2' ? 'active' : '' }}">
                                    <a href="{{ url('daily') }}" aria-expanded="false"><i
                                            class="mdi mdi-gift"></i><span class="hide-menu">Bonus Harian</span></a>
                                </li>
                                <li class="{{ request()->segment(1) == 'daily' ? 'active' : '' }}">
                                    <a href="{{ url('daily') }}" aria-expanded="false"><i
                                            class="mdi mdi-gift"></i><span class="hide-menu">Bonus Mingguan</span></a>
                                </li>
                                <li class="{{ request()->segment(1) == 'monthly' ? 'active' : '' }}">
                                    <a href="{{ url('monthly') }}" aria-expanded="false"><i
                                            class="mdi mdi-gift"></i><span class="hide-menu">Bonus Bulanan</span></a>
                                </li>
                            @endif
                            <li>
                                <a href="{{ url('a/dashboard') }}" aria-expanded="false"><i
                                        class="mdi mdi-store"></i><span class="hide-menu">Belanja Online
                                        Panel</span></a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('logout') }}" aria-expanded="false"><i class="mdi mdi-power"></i><span
                                    class="hide-menu">Keluar</span></a>
                        </li>
                    </ul>
                </nav>
                <!-- End Sidebar navigation -->
            </div>
        </aside>
        <div class="page-wrapper">
            @yield('content')
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer">
                © 2025 <a href="{{ url('/') }}">MG Network</a>
            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="{{ asset('material-pro/assets/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="{{ asset('material-pro/assets/plugins/popper/popper.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="{{ asset('material-pro/material/js/jquery.slimscroll.js') }}"></script>
    <!--Wave Effects -->
    <script src="{{ asset('material-pro/material/js/waves.js') }}"></script>
    <!--Menu sidebar -->
    <script src="{{ asset('material-pro/material/js/sidebarmenu.js') }}"></script>
    <!--stickey kit -->
    <script src="{{ asset('material-pro/assets/plugins/sticky-kit-master/dist/sticky-kit.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/sparkline/jquery.sparkline.min.js') }}"></script>
    <!--Custom JavaScript -->
    <script src="{{ asset('material-pro/material/js/custom.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/toast-master/js/jquery.toast.js') }}"></script>
    @if (Session::has('success'))
        <script type="text/javascript">
            $(function() {
                'use strict'
                $.toast({
                    heading: 'Berhasil',
                    text: '{!! Session::pull('success') !!}',
                    showHideTransition: 'slide',
                    icon: 'success'
                });
            })
        </script>
    @endif
    @if (Session::has('fail'))
        <script type="text/javascript">
            $(function() {
                'use strict'
                $.toast({
                    heading: 'Gagal',
                    text: '{!! Session::pull('fail') !!}',
                    showHideTransition: 'slide',
                    icon: 'error'
                })
            })
        </script>
    @endif
    @yield('script')
    <script>
        var login = function() {
            $.ajax({
                url: "https://campreseller.com/member-service/login/verify",
                data: {
                    action: 'login',
                    csrf_token: '{{ csrf_token() }}'
                },
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader("Authorization", "Basic " + btoa(
                        "{{ auth()->user()->username }}" +
                        ":" + "{{ auth()->user()->password }}"));
                },
                success(result, status, xhr) {
                    window.location.assign(result.data.redirect);
                },
                error(xhr, status, error) {
                    $.each(xhr.responseJSON.error, function(key, value) {
                        console.log(value);
                    });
                }
            });
        };
        $(function() {
            $('form').submit(function() {
                $(this).find('button[type=submit]').prop('disabled', true);
            });
        });
    </script>
</body>

</html>
