@extends('layout.app')
@section('title', 'Konfigurasi Harian')
@section('style')
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
        $pairs = \App\Models\Pair::orderBy('date', 'desc')->get();
        $pair_rewards = \App\Models\PairReward::orderBy('date', 'desc')->get();
    @endphp
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Konfigurasi Harian</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="#">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Konfigurasi Harian</li>
                </ol>
            </div>
            <div class="col-md-7 col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end">
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <a href=".add-pair" class="float-right btn btn-sm btn-rounded btn-danger" data-toggle="modal">Tambah
                    Tanggal</a>
                <div class="switch">
                    <label>OFF<input type="checkbox"
                            {{ \App\Models\KeyValue::where('key', 'pair')->value('value') == 'enable' ? 'checked' : '' }}
                            onchange="doToggle('pair')" id="pair"><span class="lever"></span>ON</label>
                </div>
                <div class="table-responsive">
                    <table class="datatable display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                        width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Poin Pasangan Cash</th>
                                <th>Jumlah Pasangan Cash</th>
                                <th>Nilai 1 Unit Pasangan Cash</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pairs as $a)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td><code>{{ $a->date }}</code></td>
                                    <td>{{ number_format($a->poin, 0, ',', '.') }}</td>
                                    <td>{{ number_format($a->pair, 0, ',', '.') }}</td>
                                    <td>{{ number_format($a->value, 0, ',', '.') }}</td>
                                    <td class="text-nowrap text-right">
                                        <a href=".delete-{{ $a->id }}" data-toggle="modal"><i
                                                class="mdi mdi-delete text-danger ml-1"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <a href=".add-pair-reward" class="float-right btn btn-sm btn-rounded btn-danger" data-toggle="modal">Tambah
                    Tanggal</a>
                <div class="switch">
                    <label>OFF<input type="checkbox"
                            {{ \App\Models\KeyValue::where('key', 'pair_reward')->value('value') == 'enable' ? 'checked' : '' }}
                            onchange="doToggle('pair-reward')" id="pair-reward"><span class="lever"></span>ON</label>
                </div>
                <div class="table-responsive">
                    <table class="datatable display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                        width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Poin Pasangan Reward</th>
                                <th>Jumlah Pasangan Reward</th>
                                <th>Nilai 1 Unit Pasangan Reward</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pair_rewards as $a)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td><code>{{ $a->date }}</code></td>
                                    <td>{{ number_format($a->poin, 0, ',', '.') }}</td>
                                    <td>{{ number_format($a->pair, 0, ',', '.') }}</td>
                                    <td>{{ number_format($a->value, 0, ',', '.') }}</td>
                                    <td class="text-nowrap text-right">
                                        <a href=".delete-pair-reward-{{ $a->id }}" data-toggle="modal"><i
                                                class="mdi mdi-delete text-danger ml-1"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade add-pair" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated fadeInDown">
                <form action="{{ route('pair.store') }}" method="POST" onsubmit="add.disabled = true;">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Tambah Konfigurasi</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="date" required>
                        </div>
                        <div class="form-group">
                            <label>Poin Pasangan</label>
                            <input type="number" class="form-control" name="poin" required>
                        </div>
                        <div class="form-group">
                            <label>Jumlah Pasangan</label>
                            <input type="number" class="form-control" name="pair" required>
                        </div>
                        <div class="form-group">
                            <label>Nilai 1 Unit Pasangan</label>
                            <input type="number" class="form-control" name="value" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add" class="btn btn-info">Buat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @foreach ($pairs as $a)
        <div class="modal fade delete-{{ $a->id }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form class="form-material" action="{{ route('pair.destroy', $a) }}" method="POST">
                        @csrf
                        @method('delete')
                        <div class="modal-body">
                            <h4>Hapus Poin</h4>
                            <p class="m-b-0">Apakah Anda yakin akan menghapus nilai pasangan?</p>
                            <small class="text-danger">*member akan menggunakan nilai pasangan riil pada tanggal ini
                                setelah dihapus</small>
                            <p class="text-right m-t-15">
                                <button type="submit" class="btn btn-danger waves-effect">Hapus</button>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <div class="modal fade add-pair-reward" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated fadeInDown">
                <form action="{{ route('pair-reward.store') }}" method="POST" onsubmit="add.disabled = true;">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Tambah Konfigurasi</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="date" required>
                        </div>
                        <div class="form-group">
                            <label>Poin Pasangan</label>
                            <input type="number" class="form-control" name="poin" required>
                        </div>
                        <div class="form-group">
                            <label>Jumlah Pasangan</label>
                            <input type="number" class="form-control" name="pair" required>
                        </div>
                        <div class="form-group">
                            <label>Nilai 1 Unit Pasangan</label>
                            <input type="number" class="form-control" name="value" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add" class="btn btn-info">Buat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @foreach ($pair_rewards as $a)
        <div class="modal fade delete-pair-reward-{{ $a->id }}">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form class="form-material" action="{{ route('pair-reward.destroy', $a) }}" method="POST">
                        @csrf
                        @method('delete')
                        <div class="modal-body">
                            <h4>Hapus Poin</h4>
                            <p class="m-b-0">Apakah Anda yakin akan menghapus nilai pasangan reward?</p>
                            <small class="text-danger">*member akan menggunakan nilai pasangan reward riil pada tanggal ini
                                setelah dihapus</small>
                            <p class="text-right m-t-15">
                                <button type="submit" class="btn btn-danger waves-effect">Hapus</button>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
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
        function doToggle(type) {
            if ($("#" + type).is(":checked")) {
                $.get("/" + type + "/enable", function(data, status) {
                    if (status == 'success') {
                        $.toast({
                            heading: 'Berhasil',
                            text: 'Konfigurasi berhasil diaktifkan',
                            showHideTransition: 'slide',
                            icon: 'success'
                        });
                    }
                });
            } else {
                $.get("/" + type + "/disable", function(data, status) {
                    if (status == 'success') {
                        $.toast({
                            heading: 'Berhasil',
                            text: 'Konfigurasi berhasil dinonaktifkan',
                            showHideTransition: 'slide',
                            icon: 'success'
                        });
                    }
                });
            }
        }
        jQuery(document).ready(function() {
            $('.datatable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                },
                "order": [
                    [1, "desc"]
                ]
            });
        });
    </script>
@endsection
