@extends('marketplace.layouts.inspinia')
@section('title')
    Register
@endsection
@section('style')
    <link href="{{ asset('inspinia/css/plugins/chosen/bootstrap-chosen.css') }}" rel="stylesheet">
    <link href="{{ asset('bootstrap-imageupload/dist/css/bootstrap-imageupload.min.css') }}" rel="stylesheet">
    <style>
        .chosen-single {
            padding: 4px 12px !important;
            border-radius: 0 !important;
        }
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="row m-b-lg">
            <div class="col-lg-12 text-center">
                <div class="navy-line"></div>
                <h1>Register</h1>
                <p>Register to get better experience with us.</p>
            </div>
        </div>
        <form class="form-horizontal" action="{{ url('register') }}" method="POST" enctype="multipart/form-data"
            onsubmit="register.disabled = true;">
            @csrf
            <div class="form-group">
                <label class="col-sm-2 control-label">Username Sponsor</label>
                <div class="col-sm-10">
                    <input type="text" name="sponsor" class="form-control" readonly
                        value="{{ isset($_COOKIE['sponsor']) ? \App\Models\User::where('username', $_COOKIE['sponsor'])->value('username') : old('sponsor') }}">
                    <span class="form-text m-b-none text-muted">Sponsor akan dikembalikan ke perusahaan apabila
                        dikosongkan.</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Username</label>
                <div class="col-sm-10">
                    <input type="text" name="username" value="{{ old('username') }}" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Password</label>
                <div class="col-sm-10">
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Confirm Password</label>
                <div class="col-sm-10">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Email</label>
                <div class="col-sm-10">
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Phone Number</label>
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-addon">+62</span>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="hr-line-dashed"></div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Profile Image</label>
                <div class="col-sm-10">
                    <div class="imageupload">
                        <div class="file-tab">
                            <label class="btn btn-default btn-file">
                                <span>Browse</span>
                                <input type="file" name="image" accept="image/*">
                            </label>
                            <button type="button" class="btn btn-default">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hr-line-dashed"></div>
            <div class="form-group">
                <div class="col-sm-4 col-sm-offset-2">
                    <button class="btn btn-primary" type="submit" name="register">Register</button>
                </div>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <!-- Chosen -->
    <script src="{{ asset('inspinia/js/plugins/chosen/chosen.jquery.js') }}"></script>
    <script src="{{ asset('bootstrap-imageupload/dist/js/bootstrap-imageupload.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // $('#province').chosen({width: "100%"});
            // $('#city').chosen({width: "100%"});
            // $('#subdistrict').chosen({width: "100%"});
            $('.imageupload').imageupload({
                maxFileSizeKb: 1024
            });
        });
        var password = document.getElementById("password"),
            confirm_password = document.getElementById("confirm_password");

        function validatePassword() {
            if (password.value != confirm_password.value) {
                confirm_password.setCustomValidity("Passwords Don't Match");
            } else {
                confirm_password.setCustomValidity('');
            }
        }
        password.onchange = validatePassword;
        confirm_password.onkeyup = validatePassword;
    </script>
@endsection
