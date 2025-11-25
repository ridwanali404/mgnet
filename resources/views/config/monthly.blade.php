@extends('layout.app')
@section('title', 'Konfigurasi Bulanan')
@section('style')
    <link href="{{ asset('material-pro/assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('material-pro/assets/plugins/bootstrap-select/bootstrap-select.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">
    <style>
        .dt-bootstrap4 {
            padding: 0 !important;
        }
    </style>
@endsection
@section('content')
    @php
        $userPoins = \App\Models\UserPoin::orderBy('date', 'desc')->get();
        $poins = \App\Models\Poin::orderBy('date', 'desc')->get();
    @endphp
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Konfigurasi Bulanan</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="#">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Konfigurasi Bulanan</li>
                </ol>
            </div>
            @if (Auth::user()->type == 'admin')
                <div class="col-md-7 col-4 align-self-center">
                    <div class="d-flex m-t-10 justify-content-end">
                        <div class="switch">
                            <label>OFF<input type="checkbox"
                                    {{ \App\Models\KeyValue::where('key', 'poin')->value('value') == 'enable' ? 'checked' : '' }}
                                    onchange="doToggle()" id="poin"><span class="lever"></span>ON</label>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if (Auth::user()->type == 'admin')
                            <a href="javascript:;" class="float-right btn btn-sm btn-rounded btn-danger" data-toggle="modal"
                                data-target=".add-month">Tambah Bulan</a>
                        @endif
                        <h3 class="card-title">Bulan</h3>
                        <div class="table-responsive">
                            <table id="table-poin" class="display nowrap table table-hover table-striped table-bordered"
                                cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Bulan</th>
                                        <th>Poin</th>
                                        @if (Auth::user()->type == 'admin')
                                            <th class="text-right">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($poins as $a)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($a->date)->translatedFormat('F Y') }}</td>
                                            <td>{{ number_format($a->poin, 0, ',', '.') }}</td>
                                            @if (Auth::user()->type == 'admin')
                                                <td class="text-nowrap text-right">
                                                    <a href="#" data-toggle="modal"
                                                        data-target=".delete-{{ $a->id }}"><i
                                                            class="mdi mdi-delete text-danger ml-1"></i> </a>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        @if (Auth::user()->type == 'admin')
                            <a href="javascript:;" class="float-right btn btn-sm btn-rounded btn-danger" data-toggle="modal"
                                data-target=".add">Tambah Poin</a>
                        @endif
                        <h3 class="card-title">Poin</h3>
                        <div class="table-responsive">
                            <table id="table-user-poin"
                                class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                                width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Bulan</th>
                                        <th>Username</th>
                                        <th>Nama</th>
                                        <th>Poin</th>
                                        @if (Auth::user()->type == 'admin')
                                            <th class="text-right">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($userPoins as $a)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($a->date)->translatedFormat('F Y') }}</td>
                                            <td><a
                                                    href="{{ url('user/' . $a->id . '/profile') }}">{{ $a->user->username }}</a>
                                            </td>
                                            <td>{{ $a->user->name }}</td>
                                            <td>{{ number_format($a->poin, 0, ',', '.') }}</td>
                                            @if (Auth::user()->type == 'admin')
                                                <td class="text-nowrap text-right">
                                                    <a href="#" data-toggle="modal"
                                                        data-target=".delete-{{ $a->id }}"><i
                                                            class="mdi mdi-delete text-danger ml-1"></i> </a>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade add-month" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content animated fadeInDown">
                    <form action="{{ route('poin.store') }}" method="POST" onsubmit="add.disabled = true;">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Tambah Poin</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Bulan</label>
                                <input type="month" class="form-control" name="date" required>
                            </div>
                            <div class="form-group">
                                <label>Poin</label>
                                <input type="number" class="form-control" name="poin" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="add" class="btn btn-info">Buat</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade add" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content animated fadeInDown">
                    <form action="{{ route('userPoin.store') }}" method="POST" onsubmit="add.disabled = true;">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Tambah Poin</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Member</label>
                                <select class="member" style="width: 100%;" name="user_id" placeholder="Cari member..."
                                    required></select>
                            </div>
                            <div class="form-group">
                                <label>Bulan</label>
                                <input type="month" class="form-control" name="date" required>
                            </div>
                            <div class="form-group">
                                <label>Poin</label>
                                <input type="number" class="form-control" name="poin" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="add" class="btn btn-info">Buat</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @foreach ($poins as $a)
            <div class="modal fade delete-{{ $a->id }}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form class="form-material" action="{{ route('poin.destroy', $a) }}" method="POST">
                            @csrf
                            @method('delete')
                            <div class="modal-body">
                                <h4>Hapus Poin</h4>
                                <p class="m-b-0">Apakah Anda yakin akan menghapus bulan?</p>
                                <small class="text-danger">*member akan menggunakan jumlah poin riil pada bulan ini setelah
                                    dihapus</small>
                                <p class="text-right m-t-15">
                                    <button type="submit" class="btn btn-danger waves-effect">Hapus</button>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
        @foreach ($userPoins as $a)
            <div class="modal fade delete-{{ $a->id }}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form class="form-material" action="{{ route('userPoin.destroy', $a) }}" method="POST">
                            @csrf
                            @method('delete')
                            <div class="modal-body">
                                <h4>Hapus Poin</h4>
                                <p class="m-b-0">Apakah Anda yakin akan menghapus poin?</p>
                                <small class="text-danger">*member akan menggunakan poin riil setelah dihapus</small>
                                <p class="text-right m-t-15">
                                    <button type="submit" class="btn btn-danger waves-effect">Hapus</button>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
@section('script')
    <script src="{{ asset('material-pro/assets/plugins/select2/dist/js/select2.full.min.js') }}" type="text/javascript">
    </script>
    <!-- This is data table -->
    <script src="{{ asset('material-pro/assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js') }}"></script>
    <!-- start - This is for export functionality only -->
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
    <!-- end - This is for export functionality only -->
    <script>
        function doToggle() {
            if ($("#poin").is(":checked")) {
                $.get("/poin/enable", function(data, status) {
                    if (status == 'success') {
                        $.toast({
                            heading: 'Berhasil',
                            text: 'Poin berhasil diaktifkan',
                            showHideTransition: 'slide',
                            icon: 'success'
                        });
                    }
                });
            } else {
                $.get("/poin/disable", function(data, status) {
                    if (status == 'success') {
                        $.toast({
                            heading: 'Berhasil',
                            text: 'Poin berhasil dinonaktifkan',
                            showHideTransition: 'slide',
                            icon: 'success'
                        });
                    }
                });
            }
        }
        jQuery(document).ready(function() {
            $('#table-poin').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                },
                "order": [
                    [1, "asc"]
                ]
            });
            $('#table-user-poin').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                },
                "order": [
                    [1, "asc"]
                ]
            });
            $(".member").select2({
                placeholder: "Cari member...",
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
            });
        });
    </script>
@endsection
