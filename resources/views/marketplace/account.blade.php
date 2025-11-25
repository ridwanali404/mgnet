@extends('marketplace.layouts.inspinia')
@section('title', 'Account')
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
<section class="animated fadeInDown">
    <div class="container">
        <div class="row m-b-lg">
            <div class="col-lg-12 text-center">
                <div class="navy-line"></div>
                <h1>Profile</h1>
                <p>This is who you are.</p>
            </div>
        </div>
        <div class="text-center">
            <img src="{{ asset($user->image_path) }}" class="img-thumbnail m-b-lg"
                style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
        </div>
        <form class="form-horizontal" action="{{ url('a/user/'.$user->id) }}" method="POST"
            enctype="multipart/form-data" onsubmit="profile.disabled = true;">
            @csrf
            @method('put')
            <div class="form-group">
                <label class="col-sm-2 control-label">Username Sponsor</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" readonly value="{{ $user->sponsor->username ?? '' }}">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Username</label>
                <div class="col-sm-10">
                    <input type="text" name="username" value="{{ $user->username }}" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Password</label>
                <div class="col-sm-10">
                    <input type="text" name="password" class="form-control">
                    <span class="form-text m-b-none text-muted">Kosongkan apabila tidak mengubah password.</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Email</label>
                <div class="col-sm-10">
                    <input type="email" name="email" value="{{ $user->email }}" class="form-control" readonly required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Phone Number</label>
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-addon">+62</span>
                        <input type="text" name="phone" value="{{ $user->phone }}" class="form-control" required>
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
                    <button class="btn btn-primary" type="submit" name="profile">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</section>

<section class="gray-section animated fadeInDown">
    <div class="container">
        <div class="row m-b-lg">
            <div class="col-lg-12 text-center">
                <div class="navy-line"></div>
                <h1>Address</h1>
                <p>This is where you are shipped.</p>
            </div>
        </div>
        <form class="form-horizontal m-b-xl"
            action="{{ $user->address ? url('address/'.$user->address->id) : url('address') }}" method="POST"
            enctype="multipart/form-data" onsubmit="addressButton.disabled = true;">
            @csrf
            @if ($user->address)
            @method('put')
            @endif
            <div class="form-group">
                <label class="col-sm-2 control-label">Name</label>
                <div class="col-sm-10">
                    <input type="text" name="name" value="{{ $user->address->name ?? '' }}" class="form-control"
                        required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Phone Number</label>
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-addon">+62</span>
                        <input type="text" name="phone" value="{{ $user->phone }}" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Address</label>
                <div class="col-md-10">
                    <textarea class="form-control" name="address" rows="5"
                        style="resize: vertical;">{!! $user->address->address ?? '' !!}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Province</label>
                <div class="col-md-10">
                    <select id="province" class="form-control" name="province_id" tabindex="2" required>
                        <option selected disabled>Select a Province</option>
                        @foreach($provinces as $a)
                        <option value="{{ $a->province_id }}"
                            {{ ($user->address->province->province_id ?? null) == $a->province_id ? 'selected' : '' }}>
                            {{ $a->province }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">City</label>
                <div class="col-md-10">
                    <select id="city" class="form-control" name="city_id" tabindex="2" required>
                        <option selected disabled>Select a City</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Subdistrict</label>
                <div class="col-md-10">
                    <select id="subdistrict" class="form-control" name="subdistrict_id" tabindex="2" required>
                        <option selected disabled>Select a Subdistrict</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Postal Code</label>
                <div class="col-sm-10">
                    <input type="text" name="postal_code" value="{{ $user->address->postal_code ?? '' }}"
                        class="form-control">
                </div>
            </div>
            <hr />
            <div class="form-group">
                <div class="col-sm-4 col-sm-offset-2">
                    <button class="btn btn-primary" type="submit" name="addressButton">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</section>

<section class="animated fadeInDown">
    <div class="container">
        <div class="row m-b-lg">
            <div class="col-lg-12 text-center">
                <div class="navy-line"></div>
                <h1>Member</h1>
                <p>Go to member panel.</p>
            </div>
        </div>
        <div class="text-center">
            <a href="{{ url('m/home') }}" class="btn btn-info dim btn-large-dim btn-outline"><i
                    class="fa fa-send"></i></a>
        </div>
    </div>
</section>
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
        $('#province').change(function() {
            $.get('{{ url("city") }}/' + this.value, function(data) {
                $('#city').html('<option selected disabled>Select a City</option>');
                $.each(data, function(i, value) {
                    $('#city').append($('<option>').text(value.type+' '+value.city_name).attr('value', value.city_id));
                });
                $('#subdistrict').html('<option selected disabled>Select a Subdistrict</option>');
            });
        });
        $('#city').change(function() {
            $.get('{{ url("subdistrict") }}/' + this.value, function(data) {
                $('#subdistrict').html('<option selected disabled>Select a Subdistrict</option>');
                $.each(data, function(i, value) {
                    $('#subdistrict').append($('<option>').text(value.subdistrict_name).attr('value', value.subdistrict_id));
                });
            });
        });
    });
</script>
@if ($user->address)
<script type="text/javascript">
    $(document).ready(function() {
        $.get('{{ url("city") }}/' + '{{ $user->address->province->province_id ?? "" }}', function(data) {
            $('#city').html('');
            $.each(data, function(i, value) {
                var option = $('<option>').text(value.type+' '+value.city_name).attr('value', value.city_id);
                if (value.city_id == '{{ $user->address->city->city_id ?? "" }}') {
                    option.attr('selected', 'selected');
                }
                $('#city').append(option);
            });
        });
        $.get('{{ url("subdistrict") }}/' + '{{ $user->address->city->city_id ?? "" }}', function(data) {
            $('#subdistrict').html('');
            $.each(data, function(i, value) {
                var option = $('<option>').text(value.subdistrict_name).attr('value', value.subdistrict_id);
                if (value.subdistrict_id == '{{ $user->address->subdistrict->subdistrict_id ?? "" }}') {
                    option.attr('selected', 'selected');
                }
                $('#subdistrict').append(option);
            });
        });
    });
</script>
@endif
@endsection