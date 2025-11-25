<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ App\Models\Customize::first()->meta_description }}">
    <meta name="keywords" content="{{ App\Models\Customize::first()->meta_keywords }}">
    <!-- Favicon icon -->
    <link rel="icon" href="{{ asset(App\Models\Customize::first()->image_path) }}" type="image/png" />
    <title>{{ App\Models\Customize::first()->title }} | Login</title>
    <!-- Bootstrap Core CSS -->
    <link href="{{ asset('material-pro/assets/plugins/bootstrap/css/bootstrap.min.css') }} " rel="stylesheet">
    <!-- toast CSS -->
    <link href="{{ asset('material-pro/assets/plugins/toast-master/css/jquery.toast.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('material-pro/material/css/style.min.css') }}" rel="stylesheet">
    <!-- You can change the theme colors from here -->
    <link href="{{ asset('material-pro/material/css/colors/red.css') }}" id="theme" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url("{{ asset('images/bg.png') }}");
            height: 100vh;
        }

        .centered {
            width: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2"
                stroke-miterlimit="10" />
        </svg>
    </div>
    <div class="centered">
        <div class="login-box card">
            <div class="card-body">
                <form class="form-horizontal form-material" id="loginform" action="{{ url('login') }}" method="POST"
                    onsubmit="loginButton.disabled = true;">
                    @csrf
                    <h3 class="box-title m-b-20">Sign In</h3>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input class="form-control" type="text" name="username" value="{{ old('username') }}"
                                required="" placeholder="Username">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                            <input class="form-control" type="password" name="password" required=""
                                placeholder="Password">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="d-flex no-block align-items-center">
                            <div class="checkbox checkbox-primary p-t-0">
                                <input id="checkbox-signup" name="remember" type="checkbox">
                                <label for="checkbox-signup"> Remember me </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-center m-t-20">
                        <div class="col-xs-12">
                            <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light"
                                type="submit" name="loginButton">Log In</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
    <!-- ============================================================== -->
    <!-- Style switcher -->
    <!-- ============================================================== -->
    <script src="{{ asset('material-pro/assets/plugins/styleswitcher/jQuery.style.switcher.js') }}"></script>

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
</body>

</html>
