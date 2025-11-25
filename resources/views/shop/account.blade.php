@extends('shop.layout.app')
@section('title', 'Profil')
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
        <h4>Profil</h4>
    </div>

    <div class="text-center">
        <img src="{{ asset($user->image_path) }}" class="img-thumbnail mb-3"
            style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
    </div>
    <form action="{{ url('a/user/'.$user->id) }}" method="POST"
        enctype="multipart/form-data" onsubmit="profile.disabled = true;">
        @csrf
        @method('put')
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Username Sponsor</label>
            <div class="col-sm-9">
                <input type="text" class="form-control sm-form-control" value="{{ $user->sponsor->username ?? '' }}" readonly>
            </div>
        </div>
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Username</label>
            <div class="col-sm-9">
                <input type="text" name="username" value="{{ $user->username }}" class="form-control sm-form-control" readonly required>
            </div>
        </div>
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Password</label>
            <div class="col-sm-9">
                <input type="text" name="password" class="form-control sm-form-control">
                <span class="form-text m-b-none text-muted">Kosongkan apabila tidak mengubah password.</span>
            </div>
        </div>
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Nama</label>
            <div class="col-sm-9">
                <input type="text" name="name" value="{{ $user->name }}" class="form-control sm-form-control" required>
            </div>
        </div>
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Email</label>
            <div class="col-sm-9">
                <input type="email" name="email" value="{{ $user->email }}" class="form-control sm-form-control" readonly required>
            </div>
        </div>
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Nomor HP</label>
            <div class="col-sm-9">
                <div class="input-group">
                    <span class="input-group-text sm-form-control">+62</span>
                    <input type="text" name="phone" value="{{ $user->phone }}" class="form-control sm-form-control" required>
                </div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Foto Profil</label>
            <div class="col-sm-9">
                <input class="form-control sm-form-control" type="file"  name="image" accept="image/*">
            </div>
        </div>
        <div class="hr-line-dashed"></div>
        <hr>
        <div class="row mb-3">
            <div class="col-sm-4 col-sm-offset-2">
                <button class="button nott fw-normal ms-1 my-0" type="submit" name="profile">Simpan</button>
            </div>
        </div>
    </form>


    <div class="fancy-title title-border mb-4 title-center">
        <h4>Alamat</h4>
    </div>
    
    <form class="form-horizontal m-b-xl"
        action="{{ $user->address ? url('address/'.$user->address->id) : url('address') }}" method="POST"
        enctype="multipart/form-data" onsubmit="addressButton.disabled = true;">
        @csrf
        @if ($user->address)
        @method('put')
        @endif
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Nama Alamat</label>
            <div class="col-sm-9">
                <input type="text" name="name" value="{{ $user->address->name ?? '' }}" class="form-control sm-form-control"
                    required>
            </div>
        </div>
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Nomor HP</label>
            <div class="col-sm-9">
                <div class="input-group">
                    <span class="input-group-text sm-form-control">+62</span>
                    <input type="text" name="phone" value="{{ $user->phone }}" class="form-control sm-form-control" required>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Alamat</label>
            <div class="col-sm-9">
                <textarea class="form-control sm-form-control" name="address" rows="5"
                    style="resize: vertical;">{!! $user->address->address ?? '' !!}</textarea>
            </div>
        </div>
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Provinsi</label>
            <div class="col-sm-9">
                <select id="province" class="form-control sm-form-control" name="province_id" tabindex="2" required>
                    <option selected disabled>Pilih Provinsi</option>
                    @foreach($provinces as $a)
                    <option value="{{ $a->province_id }}"
                        {{ ($user->address->province->province_id ?? null) == $a->province_id ? 'selected' : '' }}>
                        {{ $a->province }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Kabupaten/Kota</label>
            <div class="col-sm-9">
                <select id="city" class="form-control sm-form-control" name="city_id" tabindex="2" required>
                    <option selected disabled>Pilih Kabupaten/Kota</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Kecamatan</label>
            <div class="col-sm-9">
                <select id="subdistrict" class="form-control sm-form-control" name="subdistrict_id" tabindex="2" required>
                    <option selected disabled>Pilih Kecamatan</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label class="nott col-sm-3 col-form-label">Kode Pos</label>
            <div class="col-sm-9">
                <input type="text" name="postal_code" value="{{ $user->address->postal_code ?? '' }}"
                    class="form-control sm-form-control">
            </div>
        </div>
        <hr />
        <div class="row mb-3">
            <div class="col-sm-4 col-sm-offset-2">
                <button class="button nott fw-normal ms-1 my-0" type="submit" name="addressButton">Simpan</button>
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