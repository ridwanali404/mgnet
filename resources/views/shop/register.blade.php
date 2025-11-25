@extends('shop.layout.app')
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
    <div class="container clearfix">

        <div class="fancy-title title-border mb-4 title-center">
            <h4>Register</h4>
        </div>

        <form action="{{ url('register') }}" method="POST" enctype="multipart/form-data" onsubmit="register.disabled = true;">
            @csrf
            <div class="row mb-3">
                <label class="nott col-sm-3 col-form-label">Username Sponsor</label>
                <div class="col-sm-9">
                    <input type="text" name="sponsor" class="form-control sm-form-control" readonly
                        value="{{ isset($_COOKIE['sponsor']) ? \App\Models\User::where('username', $_COOKIE['sponsor'])->value('username') : old('sponsor') }}">
                    <span class="form-text m-b-none text-muted">Sponsor akan dikembalikan ke perusahaan apabila
                        dikosongkan.</span>
                </div>
            </div>
            <div class="row mb-3">
                <label class="nott col-sm-3 col-form-label">Nama</label>
                <div class="col-sm-9">
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control sm-form-control"
                        required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="nott col-sm-3 col-form-label">Username</label>
                <div class="col-sm-9">
                    <input type="text" name="username" value="{{ old('username') }}" class="form-control sm-form-control"
                        required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="nott col-sm-3 col-form-label">Password</label>
                <div class="col-sm-9">
                    <input type="password" id="password" name="password" class="form-control sm-form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="nott col-sm-3 col-form-label">Konfirmasi Password</label>
                <div class="col-sm-9">
                    <input type="password" id="confirm_password" name="confirm_password"
                        class="form-control sm-form-control" required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="nott col-sm-3 col-form-label">Email</label>
                <div class="col-sm-9">
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control sm-form-control"
                        required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="nott col-sm-3 col-form-label">Nomor HP</label>
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text sm-form-control">+62</span>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                            class="form-control sm-form-control" required>
                    </div>
                </div>
            </div>
            @if (false)
                <div class="hr-line-dashed"></div>
                <div class="row mb-3">
                    <label class="nott col-sm-3 col-form-label">Profile Image</label>
                    <div class="col-sm-9">
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
            @endif
            <hr>
            <div class="hr-line-dashed"></div>
            <div class="row mb-3">
                <div class="col-sm-4 col-sm-offset-2">
                    <button class="button nott fw-normal ms-1 my-0" type="submit" name="register">Register</button>
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
