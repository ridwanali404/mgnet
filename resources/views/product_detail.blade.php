@extends('layout.app')
@section('title', $product->name)
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
            <div class="col-8 align-self-center">
                <h3 class="text-themecolor mb-0">Produk</h3>
                <ol class="breadcrumb mb-0 p-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('a/product') }}">Produk</a></li>
                    <li class="breadcrumb-item active">{{ $product->name }}</li>
                </ol>
            </div>
            <div class="col-4 align-self-center">
                <div class="d-flex justify-content-end">
                    <div class="d-flex ml-2">
                        @if (Auth::user()->type == 'admin' ||
                            (Auth::user()->type == 'cradmin' && in_array('Konten Web', Auth::user()->roles ?? [])))
                            <button type="button" data-toggle="modal" data-target="#create"
                                class="btn btn-danger btn-rounded ml-2"><i class="mdi mdi-plus"></i><span
                                    class="d-none d-sm-inline">&nbsp;Buat
                                    Sub Produk</span></button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-body">
            <h3 class="box-title m-b-0">{{ $product->name }}</h3>
            <p class="text-muted m-b-30 font-13"> Ubah jumlah bulan </p>
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <form action="{{ url('a/product/' . $product->id) }}" method="post">
                        @method('put')
                        @csrf
                        <input type="hidden" name="is_ro" value="{{ $product->is_ro }}">
                        <input type="hidden" name="is_hidden" value="{{ $product->is_hidden }}">
                        <input type="hidden" name="is_big" value="{{ $product->is_big }}">
                        <div class="form-group">
                            <label for="month">Jumlah Bulan</label>
                            <input type="number" name="month" class="form-control" min="1" max="12"
                                step="1" id="month" value="{{ $product->month }}">
                        </div>
                        <button type="submit" class="btn btn-success waves-effect waves-light m-r-10">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table" class="display nowrap table table-hover table-striped table-bordered"
                        cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-nowrap">Dibuat pada</th>
                                <th>Nama Produk</th>
                                <th class="text-right">Poin</th>
                                <th class="text-right">Jumlah</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bigProducts as $a)
                                <tr>
                                    <td class="text-center">{{ number_format($loop->index + 1) }}</td>
                                    <td class="text-nowrap"><code>{{ $a->created_at }}</code></td>
                                    <td>{{ $a->product_name }}</td>
                                    <td class="text-nowrap text-right">
                                        <code>{{ number_format($a->product_poin, 0, ',', '.') }}</code></td>
                                    <td class="text-nowrap text-right">
                                        <code>{{ number_format($a->qty, 0, ',', '.') }}</code></td>
                                    <td class="text-nowrap text-right">
                                        <a href="#" data-toggle="modal" data-target=".update-{{ $a->id }}"><i
                                                class="mdi mdi-pencil text-inverse mr-2"></i></a>
                                        <a href="#" data-toggle="modal" data-target=".delete-{{ $a->id }}"><i
                                                class="mdi mdi-delete text-danger"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="create" class="modal fade" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('bigProduct.store') }}" method="POST" class="form-quill"
                    onsubmit="create.disabled = true; return true;">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <div class="modal-header">
                        <h4 class="modal-title">Buat Sub Produk</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Sub Produk</label>
                            <select class="custom-select col-12" name="child_product_id">
                                <option selected disabled>Pilih produk...</option>
                                @foreach (\App\Models\Product::orderBy('name')->get() as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jumlah</label>
                            <input type="number" class="form-control" min="1" step="1" name="qty"
                                required />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-rounded" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info btn-rounded" name="create">Buat Sub Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @foreach ($bigProducts as $a)
        <div class="modal fade update-{{ $a->id }}" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('bigProduct.update', $a) }}" method="POST">
                        @csrf
                        @method('put')
                        <div class="modal-header">
                            <h4 class="modal-title">Ubah Sub Produk</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Sub Produk</label>
                                <select class="custom-select col-12" name="child_product_id">
                                    <option selected disabled>Pilih produk...</option>
                                    @foreach (\App\Models\Product::orderBy('name')->get() as $product)
                                        <option value="{{ $product->id }}"
                                            {{ $a->child_product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Jumlah</label>
                                <input type="number" class="form-control" min="1" step="1" name="qty"
                                    value="{{ $a->qty }}" required />
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-info">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade delete-{{ $a->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form class="form-material" action="{{ route('bigProduct.destroy', $a->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('delete')
                        <div class="modal-body">
                            <h4>Hapus</h4>
                            <p>Apakah anda yakin?</p>
                            <p class="text-right">
                                <button type="submit" class="btn btn-danger waves-effect">Hapus</button>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <div class="invisible">{{ Session::pull('fail') }}</div>
@endsection
@section('script')
    <!-- This is data table -->
    <script src="{{ asset('material-pro/assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#table').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
                },
                "order": [
                    [1, "desc"]
                ],
                "columnDefs": [{
                    "targets": [0, 3, 4, 5],
                    "orderable": false
                }]
            });
        });
    </script>
    @if (Session::has('fail'))
        <script>
            $("#create").modal('show');
        </script>
    @endif
@endsection
