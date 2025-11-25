@extends('layout.app')
@section('title', 'Profil')
@section('style')
    <link href="{{ asset('material-pro/assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('material-pro/assets/plugins/bootstrap-select/bootstrap-select.min.css') }}" rel="stylesheet" />
@endsection
@section('content')
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Profil</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Profil</li>
                </ol>
            </div>
            <div class="col-md-7 col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end align-items-center">
                    <span class="text-{{ $user->color() }}"><i class="mdi mdi-crown"></i>
                        {{ $user->userPin->pin->name_short ?? '' }}</span>
                    @if ($user->type != 'admin' && Auth::id() == $user->id)
                        @if ($user->upgradeablePins()->count())
                            <a href="#upgrade" class="btn btn-danger btn-rounded waves-effect waves-light ml-2"
                                data-toggle="modal">Upgrade</a>
                        @elseif($user->usableUserPins()->count() && !$user->premiumUserPin)
                            <a href="#join" class="btn btn-danger btn-rounded waves-effect waves-light ml-2"
                                data-toggle="modal">Join</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Profil</h3>
            </div>
            <div class="card-body">
                <form onsubmit="update.disabled = true; return true;" method="POST"
                    action="{{ url('user/' . $user->id . '/profile') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-horizontal">
                        <div class="form-group row">
                            <label for="sponsor" class="col-sm-3 text-right control-label col-form-label">Sponsor</label>
                            <div class="col-sm-9">
                                <input id="sponsor" type="text" class="form-control"
                                    value="{{ $user->sponsor->username ?? '' }}" disabled>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="sponsor" class="col-sm-3 text-right control-label col-form-label">Nomor HP
                                Sponsor</label>
                            <div class="col-sm-9">
                                <div class="input-group m-b">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">+62</span>
                                    </div>
                                    <input id="sponsor" type="text" class="form-control"
                                        value="{{ $user->sponsor->phone ?? '' }}" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="sponsor" class="col-sm-3 text-right control-label col-form-label">Email
                                Sponsor</label>
                            <div class="col-sm-9">
                                <input id="sponsor" type="text" class="form-control"
                                    value="{{ $user->sponsor->email ?? '' }}" disabled>
                            </div>
                        </div>
                        <hr />

                        <div class="form-group row{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-sm-3 text-right control-label col-form-label">Nama
                                Lengkap</label>
                            <div class="col-sm-9">
                                <input id="name" type="text" class="form-control" name="name"
                                    value="{{ $user->name }}">
                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="pin" class="col-sm-3 text-right control-label col-form-label">Pin</label>
                            <div class="col-sm-9">
                                <input id="pin" type="text" class="form-control"
                                    value="{{ $user->userPin->pin->name ?? '' }}" disabled>
                            </div>
                        </div>

                        <div class="form-group row{{ $errors->has('phone') ? ' has-error' : '' }}">
                            <label for="phone" class="col-sm-3 text-right control-label col-form-label">Nomor
                                HP</label>
                            <div class="col-sm-9">
                                <div class="input-group m-b">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">+62</span>
                                    </div>
                                    <input id="phone" type="text" class="form-control" name="phone"
                                        value="{{ $user->phone }}">
                                </div>
                                @if ($errors->has('phone'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if (Auth::id() == $user->id || Auth::user()->type == 'admin')
                            <div class="form-group row">
                                <label for="address"
                                    class="col-sm-3 text-right control-label col-form-label">Alamat</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" id="address" cols="30" rows="5" readonly>{!! $user->address->address ?? '' !!}</textarea>
                                    @if (Auth::id() == $user->id)
                                        <span class="help-block">
                                            <a href="{{ url('account') }}">
                                                <strong>Ubah alamat</strong>
                                            </a>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <hr />

                        <div class="form-group row{{ $errors->has('username') ? ' has-error' : '' }}">
                            <label for="username"
                                class="col-sm-3 text-right control-label col-form-label">Username</label>
                            <div class="col-sm-9">
                                <input id="username" type="text" class="form-control" name="username"
                                    value="{{ $user->username }}" required>
                                @if ($errors->has('username'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-sm-3 text-right control-label col-form-label">Email</label>
                            <div class="col-sm-9">
                                <input id="email" type="text" class="form-control" name="email"
                                    value="{{ $user->email }}"
                                    @if (Auth::user()->type != 'admin') disabled @else required @endif>
                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if (Auth::id() == $user->id || Auth::user()->type == 'admin')
                            <div class="form-group row{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="password"
                                    class="col-sm-3 text-right control-label col-form-label">Password</label>
                                <div class="col-sm-9">
                                    <input id="password" type="text" class="form-control" name="password"
                                        value="{{ old('password') }}" @if (Auth::user()->type != 'admin') disabled @endif>
                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <hr />
                            <div class="form-group row{{ $errors->has('ktp') ? ' has-error' : '' }}">
                                <label for="ktp" class="col-sm-3 text-right control-label col-form-label">KTP</label>
                                <div class="col-sm-9">
                                    <input id="ktp" type="text" class="form-control" name="ktp"
                                        value="{{ $user->ktp }}">
                                    @if ($errors->has('ktp'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('ktp') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <hr />
                            <div class="form-group row">
                                <label for="bank_name"
                                    class="col-sm-3 text-right control-label col-form-label">Bank</label>
                                <div class="col-sm-9">
                                    <select name="bank_id" class="select2" style="width: 100%;"
                                        @if (Auth::user()->type != 'admin') disabled @endif>
                                        <option value="" disabled {{ !$user->bank ? 'selected' : '' }}>Pilih
                                            bank...
                                        </option>
                                        @foreach (\App\Models\Bank::orderBy('code')->get() as $a)
                                            <option value="{{ $a->id }}"
                                                {{ $user->bank ? ($user->bank->id == $a->id ? 'selected' : '') : '' }}>
                                                {{ $a->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row{{ $errors->has('bank_account') ? ' has-error' : '' }}">
                                <label for="bank_account" class="col-sm-3 text-right control-label col-form-label">Nomor
                                    Rekening</label>
                                <div class="col-sm-9">
                                    <input id="bank_account" type="text" class="form-control" name="bank_account"
                                        value="{{ $user->bank_account }}"
                                        @if (Auth::user()->type != 'admin') disabled @endif>
                                    @if ($errors->has('bank_account'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('bank_account') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row{{ $errors->has('bank_as') ? ' has-error' : '' }}">
                                <label for="bank_as" class="col-sm-3 text-right control-label col-form-label">Atas
                                    Nama</label>
                                <div class="col-sm-9">
                                    <input id="bank_as" type="text" class="form-control" name="bank_as"
                                        value="{{ $user->bank_as }}" @if (Auth::user()->type != 'admin') disabled @endif>
                                    @if ($errors->has('bank_as'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('bank_as') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <hr />
                            <div class="form-group row{{ $errors->has('npwp') ? ' has-error' : '' }}">
                                <label for="npwp"
                                    class="col-sm-3 text-right control-label col-form-label">NPWP</label>
                                <div class="col-sm-9">
                                    <input id="npwp" type="text" class="form-control" name="npwp"
                                        value="{{ $user->npwp }}">
                                    @if ($errors->has('npwp'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('npwp') }}</strong>
                                        </span>
                                    @endif
                                    <span class="help-block text-muted">
                                        <small>
                                            Kosongkan apabila tidak memiliki NPWP
                                        </small>
                                    </span>
                                </div>
                            </div>
                        @endif
                        @if (Auth::id() == $user->id || Auth::user()->type == 'admin')
                            <hr />
                            <div class="form-group row">
                                <div class="col-sm-4 offset-sm-3">
                                    <button type="submit" name="update" class="btn btn-info">
                                        Save changes
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    @if ($user->type != 'admin' && Auth::id() == $user->id)
        @if ($user->upgradeablePins()->count())
            <div class="modal inmodal" id="upgrade" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content animated fadeInDown">
                        <form action="{{ url('user/' . $user->id . '/upgrade') }}" method="POST"
                            onsubmit="upgrade.disabled = true;">
                            @csrf
                            @method('put')
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Upgrade</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="control-label">Pin</label>
                                    <select name="pin_id" class="select2" style="width: 100%;" required>
                                        <option disabled selected>Pilih pin...</option>
                                        @foreach ($user->upgradeablePins as $a)
                                            <option value="{{ $a->id }}">CR {{ $a->code }}
                                                ({{ $a->pin->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="upgrade" class="btn btn-info">Upgrade</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
        @if ($user->usableUserPins()->count() && !$user->premiumUserPin)
            <div class="modal inmodal" id="join" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content animated fadeInDown">
                        <form action="{{ url('user/' . $user->id . '/upgrade') }}" method="POST"
                            onsubmit="upgrade.disabled = true;">
                            @csrf
                            @method('put')
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Join</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="control-label">Pin</label>
                                    <select name="pin_id" class="select2" style="width: 100%;" required>
                                        <option disabled selected>Pilih pin...</option>
                                        @foreach ($user->usableUserPins as $a)
                                            <option value="{{ $a->id }}">CR {{ $a->code }}
                                                ({{ $a->pin->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="upgrade" class="btn btn-info">Join</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif
@endsection
@section('script')
    <script src="{{ asset('material-pro/assets/plugins/select2/dist/js/select2.full.min.js') }}" type="text/javascript">
    </script>
    <script>
        jQuery(document).ready(function() {
            // For select 2
            $(".select2").select2();
        });
    </script>
@endsection
