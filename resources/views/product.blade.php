@extends('layout.app')
@section('title', 'Produk')
@section('style')
    <link rel="stylesheet" type="text/css"
        href="{{ asset('material-pro/assets/plugins/summernote/dist/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('material-pro/assets/plugins/dropify/dist/css/dropify.min.css') }}">
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

        .preview-images-zone {
            width: 100%;
            border: 1px solid #ddd;
            min-height: 180px;
            /* display: flex; */
            padding: 5px 5px 0px 5px;
            position: relative;
            overflow: auto;
            border-radius: .25rem;
        }

        .preview-images-zone>.preview-image:first-child {
            height: 185px;
            width: 185px;
            position: relative;
            margin-right: 5px;
        }

        .preview-images-zone>.preview-image {
            height: 90px;
            width: 90px;
            position: relative;
            margin-right: 5px;
            float: left;
            margin-bottom: 5px;
        }

        .preview-images-zone>.preview-image>.image-zone {
            width: 100%;
            height: 100%;
        }

        .preview-images-zone>.preview-image>.image-zone>img {
            width: 100%;
            height: 100%;
            border-radius: .25rem;
        }

        .preview-images-zone>.preview-image>.tools-edit-image {
            position: absolute;
            z-index: 100;
            color: #fff;
            bottom: 0;
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
            display: none;
        }

        .preview-images-zone>.preview-image>.image-cancel {
            font-size: 18px;
            position: absolute;
            top: 0;
            right: 0;
            font-weight: bold;
            margin-right: 10px;
            cursor: pointer;
            display: none;
            z-index: 100;
        }

        .preview-image:hover>.image-zone {
            cursor: move;
            opacity: .5;
        }

        .preview-image:hover>.tools-edit-image,
        .preview-image:hover>.image-cancel {
            display: block;
        }

        .ui-sortable-helper {
            width: 90px !important;
            height: 90px !important;
        }

        .tools-edit-image {
            margin-bottom: 25px !important;
        }

        .container {
            position: absolute;
            z-index: 100;
            color: #fff;
            bottom: 0;
            text-align: center;
            margin-bottom: 10px;
            display: none;
        }

        .pro-img {
            object-fit: cover;
        }

        .modal {
            overflow-y: auto;
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
                    <li class="breadcrumb-item active">Produk</li>
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
                                    Produk</span></button>
                        @endif
                    </div>
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
                                <th class="text-center">Gambar</th>
                                <th class="text-center">Deskripsi</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $a)
                                <tr>
                                    <td class="text-center">{{ number_format($loop->index + 1) }}</td>
                                    <td class="text-nowrap"><code>{{ $a->created_at }}</code></td>
                                    <td>{{ $a->name }}</td>
                                    <td class="text-center">
                                        @if ($a->images)
                                            <a href="#" data-toggle="modal"
                                                data-target=".image-{{ $a->id }}">lihat gambar</a>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($a->desc)
                                            <a href="#" data-toggle="modal"
                                                data-target=".desc-{{ $a->id }}">lihat isi</a>
                                        @endif
                                    </td>
                                    <td class="text-nowrap text-right">
                                        @if ($a->is_big)
                                            <a href="{{ url('a/product/' . $a->id . '/edit') }}"><i
                                                    class="mdi mdi-eye mr-2"></i></a>
                                        @endif
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
                <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data" class="form-quill"
                    onsubmit="create.disabled = true; return true;">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">Buat Produk</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Kategori Produk</label>
                                <select class="custom-select col-12" name="category_id">
                                    <option selected disabled>Pilih kategori...</option>
                                    @foreach (\App\Models\Category::orderBy('name')->get() as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                {{-- <span class="help-block text-muted">
								<small>Diisi apabila bukan <strong>Produk Repeat Order</strong></small>
							</span> --}}
                            </div>
                            <div class="form-group col-md-6">
                                <label>Nama Produk</label>
                                <input type="text" class="form-control" name="name" required />
                            </div>
                            <div class="form-group col-md-6">
                                <label>Harga</label>
                                <input type="number" class="form-control" min="0" step="1" name="price"
                                    required />
                            </div>
                            <div class="form-group col-md-6">
                                <label>Harga Member</label>
                                <input type="number" class="form-control" min="0" step="1"
                                    name="price_member" required />
                                {{-- <span class="help-block text-muted">
								<small>Diisi apabila bukan <strong>Produk Repeat Order</strong></small>
							</span> --}}
                            </div>
                            <div class="form-group col-md-6">
                                <label>Harga Stokis</label>
                                <input type="number" class="form-control" min="0" step="1"
                                    name="price_stockist" required />
                            </div>
                            <div class="form-group col-md-6">
                                <label>Harga Master Stockist</label>
                                <input type="number" class="form-control" min="0" step="1"
                                    name="price_master" required />
                            </div>
                            <div class="form-group col-md-6">
                                <label>Poin</label>
                                <input type="number" class="form-control" min="0" name="poin" />
                                {{-- <span class="help-block text-muted">
								<small>Diisi apabila <strong>Produk Repeat Order</strong></small>
							</span> --}}
                            </div>
                            <div class="form-group col-md-12">
                                <label>Berat (gram)</label>
                                <input type="number" class="form-control" min="0" step="1"
                                    name="weight" />
                                {{-- <span class="help-block text-muted">
								<small>Diisi apabila bukan <strong>Produk Repeat Order</strong></small>
							</span> --}}
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="checkbox" name="is_ro" value="1" id="is_ro">
                            <label for="is_ro">Produk Repeat Order</label>
                        </div>
                        <div class="form-group">
                            <input type="checkbox" name="is_weekly" value="1" id="is_weekly">
                            <label for="is_weekly">Produk Syarat Mingguan</label>
                        </div>
                        <div class="form-group m-b-0">
                            <label>Deskripsi</label>
                            <textarea name="desc" class="form-control summernote" rows="5">{!! old('desc') !!}</textarea>
                        </div>

                        <fieldset class="form-group">
                            <a class="btn btn-rounded btn-block btn-info" href="javascript:void(0)"
                                onclick="$('#pro-image').click()">Unggah Gambar</a>
                            <input type="file" id="pro-image" name="images[]" style="display: none;"
                                class="form-control" multiple>
                        </fieldset>
                        <div id="preview-images-zone" class="preview-images-zone" style="margin-bottom: 25px;"></div>

                        <div class="form-group">
                            <label>Video Youtube Embed</label>
                            <input type="text" class="form-control" name="youtube" />
                        </div>

                        <div class="form-group">
                            <input type="checkbox" name="is_hidden" value="1" id="is_hidden">
                            <label for="is_hidden">Sembunyikan Produk</label>
                        </div>

                        <div class="form-group">
                            <input type="checkbox" name="is_big" value="1" id="is_big">
                            <label for="is_big">Paket Besar</label>
                        </div>
                        <div id="month"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light btn-rounded" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info btn-rounded" name="create">Buat Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @foreach ($products as $a)
        <div class="modal fade update-{{ $a->id }}" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('product.update', $a) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('put')
                        <div class="modal-header">
                            <h4 class="modal-title">Ubah Produk</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Kategori Produk</label>
                                    <select class="custom-select col-12" name="category_id">
                                        <option selected disabled>Pilih kategori...</option>
                                        @foreach (\App\Models\Category::orderBy('name')->get() as $category)
                                            <option value="{{ $category->id }}"
                                                {{ $a->category_id == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    {{-- <span class="help-block text-muted">
								<small>Diisi apabila bukan <strong>Produk Repeat Order</strong></small>
							</span> --}}
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Nama Produk</label>
                                    <input type="text" class="form-control" name="name"
                                        value="{{ $a->name }}" required />
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Harga Konsumen</label>
                                    <input type="number" class="form-control" min="0" step="1"
                                        name="price" value="{{ $a->price }}" required />
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Harga Member</label>
                                    <input type="number" class="form-control" min="0" step="1"
                                        name="price_member" value="{{ $a->price_member }}" required />
                                    {{-- <span class="help-block text-muted">
								<small>Diisi apabila bukan <strong>Produk Repeat Order</strong></small>
							</span> --}}
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Harga Stokis</label>
                                    <input type="number" class="form-control" min="0" step="1"
                                        name="price_stockist" value="{{ $a->price_stockist }}" required />
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Harga Master Stokis</label>
                                    <input type="number" class="form-control" min="0" step="1"
                                        name="price_master" value="{{ $a->price_master }}" required />
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Poin</label>
                                    <input type="number" class="form-control" min="0" name="poin"
                                        value="{{ $a->poin }}" />
                                    {{-- <span class="help-block text-muted">
								<small>Diisi apabila <strong>Produk Repeat Order</strong></small>
							</span> --}}
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Berat (gram)</label>
                                    <input type="number" class="form-control" min="0" step="1"
                                        name="weight" value="{{ $a->weight }}" />
                                    {{-- <span class="help-block text-muted">
								<small>Diisi apabila bukan <strong>Produk Repeat Order</strong></small>
							</span> --}}
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="checkbox" name="is_ro" value="1" id="is_ro_{{ $a->id }}"
                                    {{ $a->is_ro ? 'checked' : '' }}>
                                <label for="is_ro_{{ $a->id }}">Produk Repeat Order</label>
                            </div>
                            <div class="form-group">
                                <input type="checkbox" name="is_weekly" value="1"
                                    id="is_weekly_{{ $a->id }}" {{ $a->is_weekly ? 'checked' : '' }}>
                                <label for="is_weekly_{{ $a->id }}">Produk Syarat Mingguan</label>
                            </div>
                            <div class="form-group m-b-0">
                                <label>Deskripsi</label>
                                <textarea name="desc" class="form-control summernote" rows="5">{!! $a->desc !!}</textarea>
                            </div>

                            <fieldset class="form-group">
                                <a class="btn btn-rounded btn-block btn-info" href="javascript:void(0)"
                                    onclick="$('#pro-image-{{ $a->id }}').click()">Unggah Gambar</a>
                                <input type="file" id="pro-image-{{ $a->id }}" name="images[]"
                                    style="display: none;" class="form-control" multiple>
                            </fieldset>
                            <div class="preview-images-zone" id="preview-images-zone-{{ $a->id }}"
                                style="margin-bottom: 25px;">
                                @if ($a->images)
                                    @foreach ($a->images as $key_image => $image)
                                        <div class="preview-image preview-show-{{ $a->id }}-{{ $key_image }}">
                                            <a onclick="showDeleteImageModal('{{ $a->id }}', '{{ $key_image }}')"
                                                href="javascript:void(0)" data-toggle="modal"
                                                class="image-cancel text-danger">x</a>
                                            <div class="image-zone image-zone-{{ $a->id }}">
                                                <img class="pro-img"
                                                    id="pro-img-{{ $a->id }}-{{ $key_image }}"
                                                    src="{{ asset('storage/' . $image) }}">
                                            </div>
                                            @if ($key_image > 0)
                                                <div class="tools-edit-image justify-content-center align-items-center ">
                                                    <button
                                                        onclick="showMainImageModal('{{ $a->id }}', '{{ $key_image }}')"
                                                        type="button" class="btn btn-warning btn-circle"><i
                                                            class="fa fa-star"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="form-group">
                                <label>Video Youtube Embed</label>
                                <input type="text" class="form-control" name="youtube"
                                    value="{{ $a->youtube }}" />
                            </div>
                            <div class="form-group">
                                <input type="checkbox" name="is_hidden" value="1"
                                    id="is_hidden_{{ $a->id }}" {{ $a->is_hidden ? 'checked' : '' }}>
                                <label for="is_hidden_{{ $a->id }}">Sembunyikan Produk</label>
                            </div>
                            <div class="form-group">
                                <input type="checkbox" name="is_big" value="1" id="is_big_{{ $a->id }}"
                                    {{ $a->is_big ? 'checked' : '' }}>
                                <label for="is_big_{{ $a->id }}">Paket Besar</label>
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
        @if ($a->desc)
            <div class="modal fade desc-{{ $a->id }}" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Isi</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body">
                            {!! $a->desc !!}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if ($a->images)
            <div class="modal fade image-{{ $a->id }}" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Isi</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="{{ url('storage/' . $a->images[0]) }}" class="img-thumbnail" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="modal fade delete-{{ $a->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form class="form-material" action="{{ route('product.destroy', $a->id) }}" method="POST"
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
        @if ($a->images)
            @foreach ($a->images as $key_image => $image)
                <div class="modal fade main-image-{{ $a->id }}-{{ $key_image }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form class="form-material"
                                action="{{ route('product.image.main', ['product' => $a, 'key' => $key_image]) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <div class="modal-body">
                                    <h4>Jadikan Gambar Utama</h4>
                                    <p>Apakah anda yakin?</p>
                                    <p class="text-right">
                                        <button type="button" class="btn btn-light"
                                            onclick="hideMainImageModal('{{ $a->id }}', '{{ $key_image }}')">Batal</button>
                                        <button type="submit" class="btn btn-info waves-effect">Simpan</button>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal fade delete-image-{{ $a->id }}-{{ $key_image }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form class="form-material"
                                action="{{ route('product.image.delete', ['product' => $a, 'key' => $key_image]) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('delete')
                                <div class="modal-body">
                                    <h4>Hapus</h4>
                                    <p>Apakah anda yakin?</p>
                                    <p class="text-right">
                                        <button type="button" class="btn btn-light"
                                            onclick="hideDeleteImageModal('{{ $a->id }}', '{{ $key_image }}')">Batal</button>
                                        <button type="submit" class="btn btn-danger waves-effect">Hapus</button>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endforeach
    <div class="invisible">{{ Session::pull('fail') }}</div>
@endsection
@section('script')
    <script src="{{ asset('material-pro/assets/plugins/summernote/dist/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/dropify/dist/js/dropify.min.js') }}"></script>
    <!-- This is data table -->
    <script src="{{ asset('material-pro/assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js') }}"></script>
    @foreach ($products as $a)
        <script>
            $(document).ready(function() {
                document.getElementById('pro-image-{{ $a->id }}').addEventListener('change', readImageEdit(
                    '{{ $a->id }}'), false);
                $(document).on('click', '.image-cancel-{{ $a->id }}', function() {
                    let no = $(this).data('no');
                    $(".preview-image.preview-show-{{ $a->id }}-" + no).remove();
                    // if ($('#preview-images-zone-{{ $a->id }} .preview-image').length == 1) {
                    // 	var output = $(".preview-show-{{ $a->id }}-0");
                    // 	var html = '<div class="image-cancel image-cancel-{{ $a->id }}" data-no="0">x</div>';
                    // 	output.append(html);
                    // }
                });
            });
        </script>
    @endforeach
    <script>
        function showDeleteImageModal(productId, keyId) {
            $('.update-' + productId).modal('hide');
            $('.delete-image-' + productId + '-' + keyId).modal({
                backdrop: 'static',
                keyboard: false
            });
        }

        function hideDeleteImageModal(productId, keyId) {
            $('.delete-image-' + productId + '-' + keyId).modal('hide');
            $('.update-' + productId).modal('show');
        }

        function showMainImageModal(productId, keyId) {
            $('.update-' + productId).modal('hide');
            $('.main-image-' + productId + '-' + keyId).modal({
                backdrop: 'static',
                keyboard: false
            });
        }

        function hideMainImageModal(productId, keyId) {
            $('.main-image-' + productId + '-' + keyId).modal('hide');
            $('.update-' + productId).modal('show');
        }

        var num = 4;

        function readImageEdit(id) {
            return function() {
                if (window.File && window.FileList && window.FileReader) {
                    var files = event.target.files; //FileList object
                    var output = $("#preview-images-zone-" + id);

                    for (let i = 0; i < files.length; i++) {
                        var file = files[i];
                        if (!file.type.match('image')) continue;

                        var picReader = new FileReader();

                        picReader.addEventListener('load', function(event) {
                            var picFile = event.target;
                            var html = '<div class="preview-image preview-show-' + id + '-' + num + '">' +
                                '<div class="image-cancel image-cancel-' + id + '" data-no="' + num +
                                '">x</div>' +
                                '<div class="image-zone image-zone-' + id +
                                '"><img class="pro-img" id="pro-img-' + id + '-' + num + '" src="' + picFile
                                .result + '"></div>' +
                                '</div>';
                            output.append(html);
                            num = num + 1;
                        });

                        picReader.readAsDataURL(file);
                    }
                    // $("#pro-image-" + id).val('');
                } else {
                    console.log('Browser not support');
                }
            };
        }

        $(document).ready(function() {
            document.getElementById('pro-image').addEventListener('change', readImage, false);

            // $(".preview-images-zone").sortable();

            $(document).on('click', '#image-cancel', function() {
                let no = $(this).data('no');
                $(".preview-image#preview-show-" + no).remove();
            });
            $('#is_big').click(function() {
                if ($(this).is(':checked')) {
                    $('#month').html(`
					<div class="form-group">
						<label>Jumlah Bulan</label>
						<input type="number" class="form-control" min="1" max="12" step="1" name="month" required />
					</div>
				`);
                } else {
                    $('#month').html('');
                }
            });
        });

        function readImage() {
            if (window.File && window.FileList && window.FileReader) {
                var files = event.target.files; //FileList object
                var output = $("#preview-images-zone");

                for (let i = 0; i < files.length; i++) {
                    var file = files[i];
                    if (!file.type.match('image')) continue;

                    var picReader = new FileReader();

                    picReader.addEventListener('load', function(event) {
                        var picFile = event.target;
                        var html = '<div class="preview-image preview-show-' + num + '">' +
                            '<div id="image-cancel" class="image-cancel" data-no="' + num + '">x</div>' +
                            '<div id="image-zone" class="image-zone"><img class="pro-img" id="pro-img-' + num +
                            '" src="' + picFile.result + '"></div>' +
                            '</div>';
                        output.append(html);
                        num = num + 1;
                    });

                    picReader.readAsDataURL(file);
                }
                // $("#pro-image").val('');
            } else {
                console.log('Browser not support');
            }
        }

        jQuery(document).ready(function() {
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
            $('.summernote').summernote({
                height: 350, // set editor height
                minHeight: null, // set minimum height of editor
                maxHeight: null, // set maximum height of editor
                focus: false, // set focus to editable area after initializing summernote
                dialogsInBody: true,
                callbacks: {
                    onImageUpload: function(data) {
                        data.pop();
                    }
                },
                toolbar: [
                    ['style', ['style']],
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                ],
            });
            $('.dropify').dropify();
        });
    </script>
    @if (Session::has('fail'))
        <script>
            $("#create").modal('show');
        </script>
    @endif
@endsection
