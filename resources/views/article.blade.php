@extends('layout.app')
@section('title', 'Artikel')
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
</style>
@endsection
@section('content')
<div class="container-fluid">
	<div class="row page-titles">
		<div class="col-8 align-self-center">
			<h3 class="text-themecolor mb-0">Artikel</h3>
			<ol class="breadcrumb mb-0 p-0 bg-transparent">
				<li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
				<li class="breadcrumb-item active">Artikel</li>
			</ol>
		</div>
		<div class="col-4 align-self-center">
			<div class="d-flex justify-content-end">
				<div class="d-flex ml-2">
					@if(Auth::user()->type == 'admin')
					<button type="button" data-toggle="modal" data-target="#create"
						class="btn btn-danger btn-rounded mr-2"><i class="mdi mdi-plus"></i><span
							class="d-none d-sm-inline">&nbsp;Buat
							Artikel</span></button>
					@endif
				</div>
			</div>
		</div>
	</div>
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table id="table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
					width="100%">
					<thead>
						<tr>
							<th class="text-center">#</th>
							<th class="text-nowrap">Dibuat pada</th>
							<th>Judul</th>
							<th class="text-center">Gambar</th>
							<th class="text-center">Isi</th>
							<th class="text-right">Aksi</th>
						</tr>
					</thead>
					<tbody>
						@foreach($articles as $a)
						<tr>
							<td class="text-center">{{ number_format($loop->index + 1) }}</td>
							<td class="text-nowrap"><code>{{ $a->created_at }}</code></td>
							<td>{{ $a->title }}</td>
							<td class="text-center">
								@if($a->image)
								<a href="#" data-toggle="modal" data-target=".image-{{ $a->id }}">lihat gambar</a>
								@endif
							</td>
							<td class="text-center">
								<a href="#" data-toggle="modal" data-target=".desc-{{ $a->id }}">lihat isi</a>
							</td>
							<td class="text-nowrap text-right">
								<a href="#" data-toggle="modal" data-target=".update-{{ $a->id }}"><i
										class="mdi mdi-pencil text-inverse mr-2"></i> </a>
								<a href="#" data-toggle="modal" data-target=".delete-{{ $a->id }}"><i
										class="mdi mdi-delete text-danger"></i> </a>
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
			<form action="{{ route('article.store') }}" method="POST" enctype="multipart/form-data" class="form-quill"
				onsubmit="create.disabled = true; return true;">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title">Buat Artikel</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>Judul</label>
						<input type="text" class="form-control" name="title" required />
					</div>
					<div class="form-group m-b-0">
						<label>Isi</label>
						<textarea name="text" class="form-control summernote" rows="5"
							required>{!! old('desc') !!}</textarea>
					</div>
					<div class="form-group">
						<label>Gambar</label>
						<input name="image" type="file" class="dropify" data-allowed-file-extensions="jpg jpeg png"
							accept="image/*" />
						<small class="form-text text-muted">Ukuran rekomendasi 900 x 350 pixel</small>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light btn-rounded" data-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-info btn-rounded" name="create">Buat Artikel</button>
				</div>
			</form>
		</div>
	</div>
</div>
@foreach($articles as $a)
<div class="modal fade update-{{ $a->id }}" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form action="{{ route('article.update', $a) }}" method="POST" enctype="multipart/form-data">
				@csrf
				@method('put')
				<div class="modal-header">
					<h4 class="modal-title">Ubah Artikel</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>Judul</label>
						<input type="text" class="form-control" name="title" value="{{ $a->title }}" required />
					</div>
					<div class="form-group m-b-0">
						<label>Isi</label>
						<textarea name="text" class="form-control summernote" rows="5"
							required>{!! $a->text !!}</textarea>
					</div>
					<div class="form-group">
						<label>Gambar</label>
						<input name="image" type="file" class="dropify" data-allowed-file-extensions="jpg jpeg png"
							accept="image/*" />
						<small class="form-text text-muted">Dikosongkan apabila tidak mengubah gambar</small>
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
<div class="modal fade desc-{{ $a->id }}" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Isi</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body">
				{!! $a->text !!}
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>
@if($a->image)
<div class="modal fade image-{{ $a->id }}" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Isi</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body text-center">
				<img src="{{ url('storage/'.$a->image) }}" class="img-thumbnail" />
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
			<form class="form-material" action="{{ route('article.destroy', $a->id) }}" method="POST"
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
<script src="{{ asset('material-pro/assets/plugins/summernote/dist/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('material-pro/assets/plugins/dropify/dist/js/dropify.min.js') }}"></script>
<!-- This is data table -->
<script src="{{ asset('material-pro/assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('material-pro/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js') }}"></script>
<script>
	jQuery(document).ready(function () {
		$('#table').DataTable({
			"language": {
				"url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
			},
			"order": [[1, "desc"]],
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
				onImageUpload: function (data) {
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
@if(Session::has('fail'))
<script>
	$("#create").modal('show');
</script>
@endif
@endsection