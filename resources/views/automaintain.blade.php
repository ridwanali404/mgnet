@extends('layout.app')
@section('title', 'Automaintain')
@section('style')
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">
    <style>
        .pagination {
            margin: 0 !important;
            float: right !important;
        }

        .dt-bootstrap4 {
            padding: 0 !important;
        }
    </style>
@endsection
@php
    $cash = auth()->user()->cash_automaintain;
    $claim = floor($cash / 2000000);
@endphp
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Automaintain</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Automaintain</li>
                </ol>
            </div>
            <div class="col-md-7 col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end">
                </div>
            </div>
        </div>
        @if (auth()->user()->type == 'admin')
            @foreach ($topups as $a)
                @if (!$a->confirm_at)
                    <div class="modal inmodal" id="confirm-{{ $a->id }}" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content animated fadeInDown">
                                <form action="{{ route('topup.confirm', $a) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('patch')
                                    <div class="modal-header">
                                        <h4 class="modal-title">Konfirmasi</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin melakukan konfirmasi?
                                        <div class="text-danger">Konfirmasi tidak dapat dibatalkan</div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-rounded btn-info">Konfirmasi</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="row">
                <div class="col-md-{{ auth()->user()->isAlreadyAutomaintain(date('Y-m')) && !$claim? '12': '6' }}">
                    <div class="card card-inverse card-cr">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="m-r-20 align-self-center">
                                    <h1 class="text-white"><i class="mdi mdi-wallet"></i></h1>
                                </div>
                                <div style="width: calc(100% - (20px + 36px));">
                                    <h3 class="card-title text-truncate">Saldo Automaintain</h3>
                                    <h6 class="card-subtitle text-truncate">Jumlah saldo automaintain Anda saat ini</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 align-self-center">
                                    <h2 class="font-light text-white text-truncate">
                                        Rp {{ number_format($cash, 0, ',', '.') }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if (!auth()->user()->isAlreadyAutomaintain(date('Y-m')))
                    <div class="col-md-6">
                        <div class="card card-inverse card-cr">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="m-r-20 align-self-center">
                                        <h1 class="text-white"><i class="mdi mdi-upload"></i></h1>
                                    </div>
                                    <div style="width: calc(100% - (20px + 36px));">
                                        <h3 class="card-title text-truncate">Topup Automaintain</h3>
                                        <h6 class="card-subtitle text-truncate">Ingin klaim atau topup automaintain Anda
                                        </h6>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 align-self-center">
                                        <h2 class="font-light text-white text-truncate pointer" data-toggle="modal"
                                            data-target="#topup">Klik Disini</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if ($claim)
                    <div class="col-md-6">
                        <div class="card card-inverse card-cr">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="m-r-20 align-self-center">
                                        <h1 class="text-white"><i class="mdi mdi-gift"></i></h1>
                                    </div>
                                    <div style="width: calc(100% - (20px + 36px));">
                                        <h3 class="card-title text-truncate">Klaim</h3>
                                        <h6 class="card-subtitle text-truncate">Klaim automaintain Anda
                                        </h6>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 align-self-center">
                                        <h2 class="font-light text-white text-truncate pointer" data-toggle="modal"
                                            data-target="#claim">Klaim Disini</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            @if (!auth()->user()->isAlreadyAutomaintain(date('Y-m')))
                <div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="m-b-15">
                                Rp {{ number_format($cash, 0, ',', '.') }} / <small class="text-muted">Rp 2.000.000</small>
                            </h3>
                            <div class="progress">
                                <div id="bar-volume" class="progress-bar bg-danger" role="progressbar"
                                    style="width: {{ round(($cash / 2000000) * 100) }}%; height: 6px;" aria-valuenow="25"
                                    aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if ($automaintains->count())
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover table-stripped m-b-0">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th>#</th>
                                            <th>Dibuat pada</th>
                                            <th>Jenis</th>
                                            <th class="text-right">Nominal (Rp)</th>
                                            <th class="text-right">Saldo (Rp)</th>
                                            <th>Uraian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($automaintains as $key => $a)
                                            <tr>
                                                <td>{{ $automaintains->firstItem() + $key }}</td>
                                                <td><code>{{ $a->created_at }}</code></td>
                                                <td>{{ $a->type }}</td>
                                                <td class="text-right">
                                                    <code>{{ number_format($a->amount, 0, ',', '.') }}</code>
                                                </td>
                                                <td class="text-right">
                                                    <code>{{ number_format($a->current, 0, ',', '.') }}</code>
                                                </td>
                                                <td>{{ $a->description }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($automaintains->hasPages())
                                <div class="card-footer">
                                    {{ $automaintains->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            @if (!auth()->user()->isAlreadyAutomaintain(date('Y-m')))
                <div class="modal inmodal" id="topup" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content animated fadeInDown">
                            <form action="{{ route('topup.store') }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myModalLabel">Topup</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Nominal</label>
                                        <input type="number" class="form-control" name="amount"
                                            value="{{ $cash >= 2000000 ? 0 : 2000000 - $cash }}" min="1" readonly
                                            required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-rounded btn-info">Topup</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
            @if ($claim)
                <div class="modal inmodal" id="claim" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content animated fadeInDown">
                            <form action="{{ route('automaintain.claim') }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h4 class="modal-title" id="myModalLabel">Klaim</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Jumlah</label>
                                        <input type="number" class="form-control" name="qty"
                                            value="{{ $claim }}" min="1" max="{{ $claim }}"
                                            required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-rounded btn-info">Klaim</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
            @foreach ($topups as $a)
                <div class="modal inmodal" id="receipt-{{ $a->id }}" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content animated fadeInDown">
                            <form action="{{ route('topup.update', $a) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('patch')
                                <div class="modal-header">
                                    <h4 class="modal-title">{{ $a->receipt ? 'Ubah' : 'Unggah' }} Bukti
                                        Pembayaran</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Bukti Pembayaran</label>
                                        <div class="custom-file mb-3">
                                            <input type="file" class="custom-file-input" name="receipt"
                                                accept="image/*" required>
                                            <label class="custom-file-label form-control"></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit"
                                        class="btn btn-rounded btn-info">{{ $a->receipt ? 'Ubah' : 'Unggah' }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @if (!$a->confirm_at)
                    <div class="modal inmodal" id="delete-{{ $a->id }}" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content animated fadeInDown">
                                <form action="{{ route('topup.destroy', $a) }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('delete')
                                    <div class="modal-header">
                                        <h4 class="modal-title">Batalkan</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin melakukan pembatalan?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-rounded btn-info">Batalkan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Histori Topup</h3>
                <div class="table-responsive">
                    <table id="topup-table" class="display nowrap table table-hover table-striped table-bordered"
                        cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Dibuat pada</th>
                                <th>Member</th>
                                <th class="text-right">Nominal (Rp)</th>
                                <th>Bukti transfer</th>
                                <th>Status</th>
                                <th>Dikonfirmasi pada</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topups as $a)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td><code>{{ $a->created_at }}</code></td>
                                    <td>
                                        @if ($a->user)
                                            <a
                                                href="{{ url('user/' . $a->user->id . '/profile') }}">{{ $a->user->username }}</a>
                                        @endif
                                    </td>
                                    <td><code> {{ number_format($a->amount, 0, ',', '.') }}</code></td>
                                    <td>
                                        @if ($a->receipt)
                                            <a data-toggle="modal" href="#receipt-image-{{ $a->id }}">
                                                <img src="{{ url('storage/' . $a->receipt) }}" style="max-width: 150px;">
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($a->confirm_at)
                                            <span class="label label-rounded label-success">Dikonfirmasi</span>
                                        @else
                                            <span class="label label-rounded label-warning">Menunggu Konfirmasi</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $a->confirm_at }}</code></td>
                                    <td class="text-right">
                                        @if (!$a->confirm_at)
                                            @if (auth()->user()->type == 'admin')
                                                <a class="btn btn-xs btn-danger btn-rounded"
                                                    href="#confirm-{{ $a->id }}" data-toggle="modal">
                                                    konfirmasi
                                                </a>
                                            @else
                                                <a class="btn btn-xs btn-danger btn-rounded"
                                                    href="#receipt-{{ $a->id }}" data-toggle="modal">
                                                    {{ $a->receipt ? 'ubah' : 'unggah' }} bukti
                                                </a>
                                                <a class="btn btn-xs btn-danger btn-rounded"
                                                    href="#delete-{{ $a->id }}" data-toggle="modal">
                                                    batalkan
                                                </a>
                                            @endif
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
    @foreach ($topups as $a)
        @if ($a->receipt)
            <div class="modal inmodal" id="receipt-image-{{ $a->id }}" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content animated fadeInDown">
                        <div class="modal-header">
                            <h4 class="modal-title">
                                Bukti
                                Pembayaran</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <img class="pointer" src="{{ url('storage/' . $a->receipt) }}" data-toggle="modal"
                                data-target="receipt-image-{{ $a->id }}" style="max-width: 100%;">
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-dismiss="modal" class="btn btn-rounded btn-info">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection
@section('script')
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
            $('#topup-table').DataTable({
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
