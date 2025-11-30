@extends('layout.app')
@section('title', 'Registrasi')
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
            <div class=" col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Registrasi</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ url('referral') }}">Referral</a>
                    </li>
                    <li class="breadcrumb-item active">Registrasi</li>
                </ol>
            </div>
            <div class=" col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end">
                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <div class="row">
            <div class="col-12">
                @if (auth()->user()->type == 'member')
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Clone</h3>
                        </div>
                        <div class="card-body">
                            <form class="form-horizontal" onsubmit="clone.disabled = true; return true;" method="POST"
                                action="{{ route('user.store') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="is_clone" value="yes" />
                                <div class="form-group row">
                                    <label class="col-sm-3 text-right control-label col-form-label">Pin</label>
                                    <div class="col-sm-9">
                                        <select name="user_pin_id" class="select2" style="width: 100%;" required>
                                            <option disabled selected>Pilih pin</option>
                                            @foreach ($userPins as $a)
                                                <option value="{{ $a->id }}">CR-{{ strtoupper($a->code) }}
                                                    ({{ $a->name }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 text-right control-label col-form-label">Upline</label>
                                    <div class="col-sm-9">
                                        <select name="upline_id" id="upline_id_clone" style="width: 100%;">
                                            <option value="">Kosongkan (akan sama dengan Sponsor)</option>
                                        </select>
                                        <small class="form-text text-muted">Jika dikosongkan, upline akan sama dengan sponsor ({{ auth()->user()->username }})</small>
                                    </div>
                                </div>
                                <div class="form-group row{{ $errors->has('username') ? ' has-error' : '' }}">
                                    <label class="col-sm-3 text-right control-label col-form-label">Username</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="username"
                                            value="{{ old('username') }}" required>
                                        @if ($errors->has('username'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('username') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <hr />
                                <div class="form-group row">
                                    <div class="col-sm-9 offset-sm-3">
                                        <button type="submit" name="clone" class="btn btn-success">
                                            Clone
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Form Registrasi</h3>
                    </div>
                    <div class="card-body">
                        <form class="form-horizontal" onsubmit="register.disabled = true; return true;" method="POST"
                            action="{{ route('user.store') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="is_clone" value="no" />
                            @if (auth()->user()->type == 'admin')
                                <div class="form-group row">
                                    <label class="col-sm-3 text-right control-label col-form-label">Sponsor</label>
                                    <div class="col-sm-9">
                                        <select name="sponsor_id" id="sponsor_id" style="width: 100%;"></select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 text-right control-label col-form-label">Upline</label>
                                    <div class="col-sm-9">
                                        <select name="upline_id" id="upline_id" style="width: 100%;">
                                            <option value="">Kosongkan (akan sama dengan Sponsor)</option>
                                        </select>
                                        <small class="form-text text-muted">Jika dikosongkan, upline akan sama dengan sponsor</small>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 text-right control-label col-form-label">Paket</label>
                                    <div class="col-sm-9">
                                        <select name="pin_id" class="select2" style="width: 100%;" required>
                                            @foreach ($pins as $a)
                                                <option value="{{ $a->id }}">{{ $a->name }} Rp
                                                    {{ number_format($a->price, 0, ',', '.') }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @else
                                <div class="form-group row">
                                    <label class="col-sm-3 text-right control-label col-form-label">Pin</label>
                                    <div class="col-sm-9">
                                        <select name="user_pin_id" class="select2" style="width: 100%;" required>
                                            <option disabled selected>Pilih pin</option>
                                            @foreach ($userPins as $a)
                                                <option value="{{ $a->id }}">CR-{{ strtoupper($a->code) }}
                                                    ({{ $a->name }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 text-right control-label col-form-label">Upline</label>
                                    <div class="col-sm-9">
                                        <select name="upline_id" id="upline_id_member" style="width: 100%;">
                                            <option value="">Kosongkan (akan sama dengan Sponsor)</option>
                                        </select>
                                        <small class="form-text text-muted">Jika dikosongkan, upline akan sama dengan sponsor ({{ auth()->user()->username }})</small>
                                    </div>
                                </div>
                            @endif
                            <hr />
                            <div class="form-group row{{ $errors->has('username') ? ' has-error' : '' }}">
                                <label class="col-sm-3 text-right control-label col-form-label">Username</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="username"
                                        value="{{ old('username') }}" required>
                                    @if ($errors->has('username'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('username') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label class="col-sm-3 text-right control-label col-form-label">Password</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="password"
                                        value="{{ old('password') }}" required>
                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label class="col-sm-3 text-right control-label col-form-label">Email</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="email"
                                        value="{{ old('email') }}" required>
                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <hr />
                            <div class="form-group row{{ $errors->has('ktp') ? ' has-error' : '' }}">
                                <label class="col-sm-3 text-right control-label col-form-label">NIK</label>
                                <div class="col-sm-9">
                                    <input type="text" minlength="16" maxlength="16" class="form-control"
                                        name="ktp" value="{{ old('ktp') }}">
                                    @if ($errors->has('ktp'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('ktp') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label class="col-sm-3 text-right control-label col-form-label">Nama</label>
                                <div class="col-sm-9">
                                    <input type="text" minlength="3" maxlength="50" class="form-control"
                                        name="name" value="{{ old('name') }}" required>
                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row{{ $errors->has('phone') ? ' has-error' : '' }}">
                                <label class="col-sm-3 text-right control-label col-form-label">Nomor
                                    HP</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">+62</span>
                                        </div>
                                        <input type="number" pattern="\d+" class="form-control" name="phone"
                                            value="{{ old('phone') }}" required>
                                    </div>
                                    @if ($errors->has('phone'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('phone') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row{{ $errors->has('npwp') ? ' has-error' : '' }}">
                                <label class="col-sm-3 text-right control-label col-form-label">NPWP</label>
                                <div class="col-sm-9">
                                    <input type="text" minlength="15" maxlength="16" class="form-control"
                                        name="npwp" value="{{ old('npwp') }}" required>
                                    @if ($errors->has('npwp'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('npwp') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <hr />
                            <div class="form-group row">
                                <label class="col-sm-3 text-right control-label col-form-label">Bank</label>
                                <div class="col-sm-9">
                                    <select name="bank_id" class="select2" style="width: 100%;" required>
                                        @foreach ($banks as $a)
                                            <option value="{{ $a->id }}">{{ $a->code }} -
                                                {{ $a->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row{{ $errors->has('bank_account') ? ' has-error' : '' }}">
                                <label class="col-sm-3 text-right control-label col-form-label">No
                                    Rekening</label>
                                <div class="col-sm-9">
                                    <input type="text" minlength="3" maxlength="50" class="form-control"
                                        name="bank_account" value="{{ old('bank_account') }}" required>
                                    @if ($errors->has('bank_account'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('bank_account') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row{{ $errors->has('bank_as') ? ' has-error' : '' }}">
                                <label class="col-sm-3 text-right control-label col-form-label">Atas
                                    Nama</label>
                                <div class="col-sm-9">
                                    <input type="text" minlength="3" maxlength="50" class="form-control"
                                        name="bank_as" value="{{ old('bank_as') }}" required>
                                    @if ($errors->has('bank_as'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('bank_as') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <hr />
                            <div class="form-group row">
                                <div class="col-sm-9 offset-sm-3">
                                    <button type="submit" name="register" class="btn btn-success">
                                        Registrasi
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
@endsection
@section('script')
    <script src="{{ asset('material-pro/assets/plugins/select2/dist/js/select2.full.min.js') }}" type="text/javascript">
    </script>
    <script>
        jQuery(document).ready(function() {
            $(".select2").select2();
        });
    </script>
    @if (auth()->user()->type == 'admin')
        <script>
            jQuery(document).ready(function() {
                $("select[name=sponsor_id]").select2({
                    placeholder: "Cari sponsor...",
                    allowClear: true,
                    ajax: {
                        url: '/filter-user',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                page: params.page || 1
                            }
                            // Query parameters will be ?search=[term]&page=[page]
                            return query;
                        },
                        processResults: function(data) {
                            return {
                                results: data.data,
                                pagination: {
                                    more: (data.current_page * data.per_page) < data.total
                                }
                            };
                        },
                        cache: true,
                    }
                }).on("select2:select", function(e) {
                    var sponsorId = e.params.data.id;
                    var currentUplineId = $("select[name=upline_id]").val();
                    
                    // Jika upline sudah dipilih dan bukan sponsor itu sendiri, clear upline
                    // Karena saat mencari upline, hanya downline dari sponsor yang akan muncul
                    // Jadi upline yang sudah dipilih mungkin tidak valid lagi jika sponsor berubah
                    if (currentUplineId && currentUplineId != sponsorId) {
                        // Clear upline untuk memastikan konsistensi
                        // User bisa memilih ulang dari daftar downline sponsor yang baru
                        $("select[name=upline_id]").val(null).trigger('change');
                    }
                    
                    // Set default upline ke sponsor jika upline masih kosong
                    if (!$("select[name=upline_id]").val()) {
                        var sponsorData = e.params.data;
                        var newOption = new Option(sponsorData.text, sponsorData.id, true, true);
                        $("select[name=upline_id]").append(newOption).trigger('change');
                    }
                }).on("select2:clear", function() {
                    // Clear upline saat sponsor di-clear
                    $("select[name=upline_id]").val(null).trigger('change');
                });
                
                $("select[name=upline_id]").select2({
                    placeholder: "Cari upline...",
                    allowClear: true,
                    ajax: {
                        url: '/filter-user',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                page: params.page || 1,
                                sponsor_id: $("select[name=sponsor_id]").val() || ''
                            }
                            return query;
                        },
                        processResults: function(data) {
                            return {
                                results: data.data,
                                pagination: {
                                    more: (data.current_page * data.per_page) < data.total
                                }
                            };
                        },
                        cache: true,
                    }
                });
            });
        </script>
    @else
        <script>
            jQuery(document).ready(function() {
                // Inisialisasi select2 untuk upline_id member
                // Hanya menampilkan downline dari user yang sedang login
                $("#upline_id_member").select2({
                    placeholder: "Cari upline...",
                    allowClear: true,
                    ajax: {
                        url: '/filter-user',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                page: params.page || 1,
                                sponsor_id: {{ auth()->id() }} // Filter berdasarkan downline user yang sedang login
                            }
                            return query;
                        },
                        processResults: function(data) {
                            return {
                                results: data.data,
                                pagination: {
                                    more: (data.current_page * data.per_page) < data.total
                                }
                            };
                        },
                        cache: true,
                    }
                });
                
                // Inisialisasi select2 untuk upline_id clone
                // Hanya menampilkan downline dari user yang sedang login
                $("#upline_id_clone").select2({
                    placeholder: "Cari upline...",
                    allowClear: true,
                    ajax: {
                        url: '/filter-user',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                page: params.page || 1,
                                sponsor_id: {{ auth()->id() }} // Filter berdasarkan downline user yang sedang login
                            }
                            return query;
                        },
                        processResults: function(data) {
                            return {
                                results: data.data,
                                pagination: {
                                    more: (data.current_page * data.per_page) < data.total
                                }
                            };
                        },
                        cache: true,
                    }
                });
            });
        </script>
    @endif
@endsection
