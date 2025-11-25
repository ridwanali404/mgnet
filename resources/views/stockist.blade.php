@extends('layout.app')
@section('title', 'Stokis')
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
            <h3 class="text-themecolor m-b-0 m-t-0">Stokis</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="#">Home</a>
                </li>
                <li class="breadcrumb-item active">Stokis</li>
            </ol>
        </div>
        @if(Auth::user()->type == 'admin')
        <div class="col-md-7 col-4 align-self-center">
            <div class="d-flex m-t-10 justify-content-end">
                <a href="#" class="btn waves-effect waves-light btn-danger pull-right" data-toggle="modal"
                    data-target=".add"> Buat Stokis</a>&nbsp;
                <a href="#" class="btn waves-effect waves-light btn-danger pull-right ml-1" data-toggle="modal"
                    data-target=".addMaster"> Buat Master Stokis</a>&nbsp;
            </div>
        </div>
        @endif
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Stokis</h3>
                    <div class="table-responsive">
                        <table id="table-stockist" class="display nowrap table table-hover table-striped table-bordered"
                            cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    @if(Auth::user()->type == 'admin')
                                    <th class="text-right">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $a)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td><a href="{{ url('user/'.$a->id.'/profile') }}">{{ $a->username }}</a></td>
                                    <td>{{ $a->name }}</td>
                                    <td>{{ $a->is_master_stockist ? 'Master Stokis' : 'Stokis' }}</td>
                                    @if(Auth::user()->type == 'admin')
                                    <td class="text-nowrap text-right">
                                        @if(!$a->is_master_stockist)
                                        <a href="#" data-toggle="modal" data-target=".master-stockist-{{ $a->id }}"><i class="mdi mdi-account-star-variant text-warning ml-1"></i> </a>
                                        @endif
                                        @if(!$a->is_stockist)
                                        <a href="#" data-toggle="modal" data-target=".stockist-{{ $a->id }}"><i class="mdi mdi-account-check text-success ml-1"></i> </a>
                                        @endif
                                        <a href="#" data-toggle="modal" data-target=".delete-{{ $a->id }}"><i class="mdi mdi-snowflake text-danger ml-1"></i> </a>
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
    <div class="modal fade add" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated fadeInDown">
                <form action="{{ route('stockist.store') }}" method="POST" onsubmit="add.disabled = true;">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Buat Stokis</h4>
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
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add" class="btn btn-info">Buat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade addMaster" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated fadeInDown">
                <form action="{{ route('masterStockist.store') }}" method="POST" onsubmit="add.disabled = true;">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Buat Master Stokis</h4>
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
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add" class="btn btn-info">Buat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @foreach ($users as $a)
    <div class="modal fade delete-{{ $a->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <form class="form-material" action="{{ route('stockist.destroy', $a) }}" method="POST">
                    @csrf
                    @method('delete')
                    <div class="modal-body">
                        <h4>Bekukan Stokis</h4>
                        <p class="m-b-0">Apakah Anda yakin akan membekukan stokis?</p>
                        <small class="text-danger">*member tidak dapat melakukan kegiatan stokis lagi apabila
                            dibekukan</small>
                        <p class="text-right m-t-15">
                            <button type="submit" class="btn btn-danger waves-effect">Bekukan</button>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if(!$a->is_master_stockist)
    <div class="modal fade master-stockist-{{ $a->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <form class="form-material" action="{{ route('masterStockist.set', $a) }}" method="POST">
                    @csrf
                    @method('put')
                    <div class="modal-body">
                        <h4>Ubah Master Stokis</h4>
                        <p class="m-b-0">Apakah Anda yakin akan mengubah member menjadi Master Stokis?</p>
                        <p class="text-right m-t-15">
                            <button type="submit" class="btn btn-danger waves-effect">Ubah</button>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @if(!$a->is_stockist)
    <div class="modal fade stockist-{{ $a->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <form class="form-material" action="{{ route('stockist.set', $a) }}" method="POST">
                    @csrf
                    @method('put')
                    <div class="modal-body">
                        <h4>Ubah Stokis</h4>
                        <p class="m-b-0">Apakah Anda yakin akan mengubah member menjadi Stokis?</p>
                        <p class="text-right m-t-15">
                            <button type="submit" class="btn btn-danger waves-effect">Ubah</button>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
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
    jQuery(document).ready(function () {
        $('#table-stockist').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
            },
            "order": [[1, "asc"]]
        });
        $(".member").select2({
            placeholder: "Cari member...",
            ajax: {
                url: '/filter-user',
                data: function (params) {
                    var query = {
                        search: params.term,
                        page: params.page || 1
                    }
                    // Query parameters will be ?search=[term]&page=[page]
                    return query;
                },
                processResults: function (data) {
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