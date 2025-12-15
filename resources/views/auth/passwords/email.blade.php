<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ App\Models\Customize::first()->meta_description }}">
    <meta name="keywords" content="{{ App\Models\Customize::first()->meta_keywords }}">
    <link rel="icon" href="{{ asset('images/mgnet-favicon.png') }}" type="image/png" />
    <title>{{ App\Models\Customize::first()->title }} | Reset Password</title>
    <link href="{{ asset('material-pro/assets/plugins/bootstrap/css/bootstrap.min.css') }} " rel="stylesheet">
    <link href="{{ asset('material-pro/material/css/style.min.css') }}" rel="stylesheet">
    <link href="{{ asset('material-pro/material/css/colors/red.css') }}" id="theme" rel="stylesheet">
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
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
        </svg>
    </div>
    <div class="centered">
        <div class="login-box card">
            <div class="card-body">
                <form class="form-horizontal form-material" action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <h3 class="box-title m-b-20">Reset Password</h3>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Email Address">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group text-center m-t-20">
                        <div class="col-xs-12">
                            <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Send Password Reset Link</button>
                        </div>
                    </div>
                    <div class="form-group m-b-0">
                        <div class="col-sm-12 text-center">
                            <p>Back to <a href="{{ route('login') }}" class="text-info m-l-5"><b>Sign In</b></a></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('material-pro/assets/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/popper/popper.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('material-pro/material/js/jquery.slimscroll.js') }}"></script>
    <script src="{{ asset('material-pro/material/js/waves.js') }}"></script>
    <script src="{{ asset('material-pro/material/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/sticky-kit-master/dist/sticky-kit.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/sparkline/jquery.sparkline.min.js') }}"></script>
    <script src="{{ asset('material-pro/material/js/custom.min.js') }}"></script>
</body>

</html>