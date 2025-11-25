@extends('layout.app')
@section('title', 'Admin CR')
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
            <h3 class="text-themecolor m-b-0 m-t-0">Admin CR</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="#">Home</a>
                </li>
                <li class="breadcrumb-item active">Admin CR</li>
            </ol>
        </div>
        @if(Auth::user()->type == 'admin')
        <div class="col-md-7 col-4 align-self-center">
            <div class="d-flex m-t-10 justify-content-end">
                <a href="#" class="btn waves-effect waves-light btn-danger pull-right ml-1" data-toggle="modal"
                    data-target=".add"> Buat Admin</a>&nbsp;
            </div>
        </div>
        @endif
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Admin</h3>
                    <div class="table-responsive">
                        <table id="table-stockist" class="display table table-hover table-striped table-bordered"
                            cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Role</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $a)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td><a href="{{ url('user/'.$a->id.'/profile') }}">{{ $a->username }}</a></td>
                                    <td>{{ $a->name }}</td>
                                    <td>
                                        <ul>
                                            @foreach ($a->roles ?? [] as $role)
                                            <li>{{ $role }}</li>
                                            @endforeach  
                                        </ul>  
                                    </td>
                                    <td class="text-nowrap text-right">
                                        <a href="#" data-toggle="modal" data-target=".edit-{{ $a->id }}"><i class="mdi mdi-pencil text-primary ml-1"></i> </a>
                                        <a href="#" data-toggle="modal" data-target=".delete-{{ $a->id }}"><i class="mdi mdi-delete text-danger ml-1"></i> </a>
                                    </td>
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
                <form action="{{ route('admin.store') }}" method="POST" onsubmit="add.disabled = true;">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Buat Admin CR</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-sm-12">
                                <label>Nama</label>
                                <input name="name" value="{{ old('name') }}" class="form-control" required />
                            </div>
                            <div class="form-group col-sm-12">
                                <label>Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required />
                            </div>
                            <div class="form-group col-sm-6">
                                <label>Username</label>
                                <input name="username" value="{{ old('username') }}" class="form-control" required />
                            </div>
                            <div class="form-group col-sm-6">
                                <label>Password</label>
                                <input name="password" class="form-control" required />
                            </div>
                            <div class="form-group col-sm-12">
                                <div class="m-b-10">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" name="roles[]" value="Konten Web" class="custom-control-input">
                                        <span class="custom-control-label">Konten Web<br /><small>Edit menu tampilan dan isi produk</small></span>
                                    </label>
                                </div>
                                <div class="m-b-10 bd-example-indeterminate">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" name="roles[]" value="Keuangan" class="custom-control-input">
                                        <span class="custom-control-label">Keuangan<br /><small>Konfirmasi pembayaran belanja online dan bonus bulanan</small></span>
                                    </label>
                                </div>
                                <div class="m-b-10">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" name="roles[]" value="Gudang" class="custom-control-input">
                                        <span class="custom-control-label">Gudang<br /><small>Konfirmasi barang sudah dikemas</small></span>
                                    </label>
                                </div>
                                <div class="m-b-10">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" name="roles[]" value="Ekspedisi" class="custom-control-input">
                                        <span class="custom-control-label">Ekspedisi<br /><small>Input resi pengiriman dan konfirmasi barang sudah dikirim</small></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add" class="btn btn-info">Buat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @foreach ($users as $key => $a)
    <div class="modal fade edit-{{ $a->id }}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated fadeInDown">
                <form action="{{ route('admin.update', $a) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Ubah Admin CR</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-sm-12">
                                <label>Nama</label>
                                <input name="name" value="{{ $a->name }}" class="form-control" required />
                            </div>
                            <div class="form-group col-sm-12">
                                <label>Email</label>
                                <input type="email" value="{{ $a->email }}" name="email" class="form-control" required />
                            </div>
                            <div class="form-group col-sm-6">
                                <label>Username</label>
                                <input name="username" value="{{ $a->username }}" class="form-control" required />
                            </div>
                            <div class="form-group col-sm-6">
                                <label>Password</label>
                                <input name="password" class="form-control" />
                                <span class="help-block text-muted">
                                    <small>
                                        Kosongkan apabila tidak mengubah password
                                    </small>
                                </span>
                            </div>
                            <div class="form-group col-sm-12">
                                <div class="m-b-10">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" name="roles[]" value="Konten Web" {{ in_array('Konten Web', $a->roles ?? []) ? 'checked' : '' }} class="custom-control-input">
                                        <span class="custom-control-label">Konten Web<br /><small>Edit menu tampilan dan isi produk</small></span>
                                    </label>
                                </div>
                                <div class="m-b-10 bd-example-indeterminate">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" name="roles[]" value="Keuangan" {{ in_array('Keuangan', $a->roles ?? []) ? 'checked' : '' }} class="custom-control-input">
                                        <span class="custom-control-label">Keuangan<br /><small>Konfirmasi pembayaran belanja online dan bonus bulanan</small></span>
                                    </label>
                                </div>
                                <div class="m-b-10">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" name="roles[]" value="Gudang" {{ in_array('Gudang', $a->roles ?? []) ? 'checked' : '' }} class="custom-control-input">
                                        <span class="custom-control-label">Gudang<br /><small>Konfirmasi barang sudah dikemas</small></span>
                                    </label>
                                </div>
                                <div class="m-b-10">
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" name="roles[]" value="Ekspedisi" {{ in_array('Ekspedisi', $a->roles ?? []) ? 'checked' : '' }} class="custom-control-input">
                                        <span class="custom-control-label">Ekspedisi<br /><small>Input resi pengiriman dan konfirmasi barang sudah dikirim</small></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-info">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade delete-{{ $a->id }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <form class="form-material" action="{{ route('admin.destroy', $a) }}" method="POST">
                    @csrf
                    @method('delete')
                    <div class="modal-body">
                        <h4>Hapus Admin</h4>
                        <p class="m-b-0">Apakah Anda yakin akan menghapus admin?</p>
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
    jQuery(document).ready(function () {
        $('#table-stockist').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
            },
            "order": [[1, "asc"]]
        });
        $.get("{{ url('api/city') }}", function(data, status){
            @foreach($users as $key => $a)
            $(".select2-{{ $key }}").select2({
                data: data,
            })
            $(".select2-{{ $key }}").val({{ $a->cities()->pluck('city.city_id') }});
            $(".select2-{{ $key }}").trigger('change');
            @endforeach
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