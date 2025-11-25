@extends('layout.app')
@section('title', 'Pin')
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
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Pin</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Pin</li>
                </ol>
            </div>
            <div class="col-md-7 col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end">
                    @if (Auth::user()->type == 'admin')
                        <a href="#" class="btn waves-effect waves-light btn-danger pull-right" data-toggle="modal"
                            data-target="#add">Pembelian PIN</a>
                    @else
                        <a href="#" class="btn waves-effect waves-light btn-danger pull-right" data-toggle="modal"
                            data-target="#transfer"> Transfer PIN</a>
                    @endif
                </div>
            </div>
        </div>
        @if (Auth::user()->type != 'admin')
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-inverse card-cr">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="m-r-20 align-self-center">
                                    <h1 class="text-white"><i class="mdi mdi-account-card-details"></i></h1>
                                </div>
                                <div style="width: calc(100% - (20px + 36px));">
                                    <h3 class="card-title text-truncate">Usable Pin</h3>
                                    <h6 class="card-subtitle text-truncate">Jumlah usable pin sekarang</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 align-self-center">
                                    <h2 class="font-light text-white text-truncate text-center">
                                        {{ Auth::user()->usableUserPins()->count() }} pin</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-inverse card-cr">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="m-r-20 align-self-center">
                                    <h1 class="text-white"><i class="mdi mdi-swap-horizontal"></i></h1>
                                </div>
                                <div style="width: calc(100% - (20px + 36px));">
                                    <h3 class="card-title text-truncate">Transfer</h3>
                                    <h6 class="card-subtitle text-truncate">Jumlah pin yang ditransfer</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 align-self-center">
                                    <h2 class="font-light text-white text-truncate text-center">
                                        {{ Auth::user()->transferPinHistories()->sum('qty') }} pin</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Histori Pembelian Pin</h3>
                        <div class="table-responsive">
                            <table id="pinBuyHistory" class="display nowrap table table-hover table-striped table-bordered"
                                cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Dibuat pada</th>
                                        <th>Member</th>
                                        <th>Paket</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($buy_pin_histories as $a)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td><code>{{ $a->created_at }}</code></td>
                                            <td><a
                                                    href="{{ url('user/' . $a->user->id . '/profile') }}">{{ $a->user->username }}</a>
                                            </td>
                                            <td>{{ $a->pin->name }}</td>
                                            <td>{{ $a->qty }} pin</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Histori Transfer Pin</h3>
                        <div class="table-responsive">
                            <table id="pinTransferHistory"
                                class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                                width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Dibuat pada</th>
                                        <th>Member</th>
                                        <th>Ditransfer ke</th>
                                        <th>Paket</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transfer_pin_histories as $a)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td><code>{{ $a->created_at }}</code></td>
                                            <td><a
                                                    href="{{ url('user/' . $a->user->id . '/profile') }}">{{ $a->user->username }}</a>
                                            </td>
                                            <td><a
                                                    href="{{ url('user/' . $a->to->id . '/profile') }}">{{ $a->to->username }}</a>
                                            </td>
                                            <td>{{ $a->pin->name }}</td>
                                            <td>{{ $a->qty }} pin</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @if (Auth::user()->type != 'admin')
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">Pin</h3>
                            <h6 class="card-subtitle">Usable pin dan pin yang telah digunakan</h6>
                            <div class="table-responsive">
                                <table id="pin" class="display nowrap table table-hover table-striped table-bordered"
                                    cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Dibuat pada</th>
                                            <th>PIN</th>
                                            <th>Paket</th>
                                            <th>Status</th>
                                            <th>Owner</th>
                                            <th class="text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($userPins as $a)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td><code>{{ $a->created_at }}</code></td>
                                                <td><code>CR-{{ strtoupper($a->code) }}</code></td>
                                                <td>{{ $a->pin->name }}</td>
                                                <td>
                                                    @if (!$a->user)
                                                        <span class="label label-warning">Belum terpakai</span>
                                                    @else
                                                        <span class="label label-success">Terpakai
                                                            ({{ $a->user->username }})
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($a->buyer)
                                                        <a
                                                            href="{{ url('user/' . $a->buyer->id . '/profile') }}">{{ $a->buyer->username }}</a>
                                                    @else
                                                        Admin
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    @if (!$a->user && Auth::user()->checkUsablePin($a->pin))
                                                        <a class="btn btn-xs btn-danger btn-rounded"
                                                            href="#use-{{ $a->id }}" data-toggle="modal">
                                                            gunakan
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @foreach ($userPins as $a)
            @if (!$a->user && Auth::user()->checkUsablePin($a->pin))
                <div class="modal inmodal" id="use-{{ $a->id }}" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content animated fadeInDown">
                            <form action="{{ url('user/' . Auth::id() . '/upgrade') }}" method="POST">
                                @csrf
                                @method('put')
                                <input type="hidden" name="pin_id" value="{{ $a->id }}">
                                <div class="modal-header">
                                    <h4 class="modal-title">Gunakan</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    Apakah Anda yakin?
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-info">Gunakan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
        @if (Auth::user()->type == 'admin')
            <div class="modal inmodal" id="add" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content animated fadeInDown">
                        <form action="{{ url('userPin/generate') }}" method="POST" onsubmit="add.disabled = true;">
                            @csrf
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Buat Pin</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="control-label">Pin</label>
                                    <select name="pin_id" class="select2" style="width: 100%;" required>
                                        @foreach ($pins as $a)
                                            <option value="{{ $a->id }}">{{ $a->name }} Rp
                                                {{ number_format($a->price) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Pembeli</label>
                                    <select id="users-add" style="width: 100%;" name="buyer_id" required></select>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Jumlah</label>
                                    <input type="number" class="form-control" name="amount" value=1 required>
                                </div>
                                <div class="form-group mb-0">
                                    <input type="checkbox" name="use" id="use" checked>
                                    <label for="use">Gunakan Pin<br><small>Isi jumlah dengan 1 apabila akan langsung
                                            menggunakan pin.</small></label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="add" class="btn btn-info">Generate</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="modal inmodal" id="transfer" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content animated fadeInDown">
                        <form action="{{ route('userPin.transfer') }}" method="POST"
                            onsubmit="transfer.disabled = true;">
                            @csrf
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalLabel">Transfer Pin</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label class="control-label">Pin</label>
                                    <select name="pin_id" class="select2" style="width: 100%;" required>
                                        @foreach ($pins as $a)
                                            <option value="{{ $a->id }}">{{ $a->name }} Rp
                                                {{ number_format($a->price) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Pembeli</label>
                                    <select id="users-transfer" style="width: 100%;" name="buyer_id" required></select>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Jumlah</label>
                                    <input type="number" class="form-control" name="amount" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="transfer" class="btn btn-info">Transfer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
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
        jQuery(document).ready(function() {
            // For select 2
            $(".select2").select2();
            $("#users-add").select2({
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
            $("#users-transfer").select2({
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
            $('#pinBuyHistory').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
            $('#pinTransferHistory').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
            $('#pin').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
        });
    </script>
@endsection
