@extends('marketplace.layouts.admin')
@section('title')
Page
@endsection
@section('style')
<!-- SUMMERNOTE -->
<link href="{{ asset('inspinia/css/plugins/summernote/summernote.css') }}" rel="stylesheet">
<link href="{{ asset('inspinia/css/plugins/summernote/summernote-bs3.css') }}" rel="stylesheet">
<!-- FooTable -->
<link href="{{ asset('inspinia/css/plugins/footable/footable.core.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="ibox float-e-margins">
	<form action="{{ url('a/page') }}" method="POST" enctype="multipart/form-data" onsubmit="add.disabled = true;">
		@csrf
		<div class="ibox-title">
			<h5>Content</h5>
			<div class="ibox-tools">
				<button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#add">
					<span>Save</span>
				</button>
			</div>
		</div>
		<div class="ibox-content no-padding">
			<textarea class="form-control summernote" name="content"></textarea>
		</div>
		<div class="modal inmodal" id="add" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content animated fadeInDown">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">
							<span aria-hidden="true">&times;</span>
							<span class="sr-only">Close</span>
						</button>
						<i class="fa fa-pencil modal-icon"></i>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label>Name</label>
							<input type="text" name="name" class="form-control" required>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" name="add" class="btn btn-primary">Save</button>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="ibox">
	<div class="ibox-title">
		<h5>Page</h5>
	</div>
	<div class="ibox-content">
		<input type="text" class="form-control input-sm m-b-xs" id="filter" placeholder="Search in table">
		<div class="table-responsive">
			<table class="footable table table-stripped toggle-arrow-tiny" data-page-size="8" data-filter=#filter>
				<thead>
					<tr>
						<th data-toggle="true">Name</th>
						<th>Date</th>
						<th data-hide="all">Content</th>
						<th class="text-right" data-sort-ignore="true">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($pages as $a)
					<tr>
						<td>{{ $a->name }}</td>
						<td>{{ $a->created_at }}</td>
						<td>{!! $a->content !!}</td>
						<td>
							<div class="btn-group pull-right">
								<button class="btn-white btn btn-xs" data-toggle="modal"
									data-target="#edit{{ $a->id }}">Edit</button>
								<button class="btn-white btn btn-xs" data-toggle="modal"
									data-target="#delete{{ $a->id }}">Delete</button>
							</div>
							<div class="modal inmodal" id="edit{{ $a->id }}" tabindex="-1" role="dialog"
								aria-hidden="true">
								<div class="modal-dialog modal-lg">
									<div class="modal-content animated fadeInDown">
										<form action="{{ url('a/page/'.$a->id) }}" method="POST"
											enctype="multipart/form-data">
											@csrf
											{{ method_field('PUT') }}
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal"><span
														aria-hidden="true">&times;</span><span
														class="sr-only">Close</span></button>
												<i class="fa fa-pencil modal-icon"></i>
											</div>
											<div class="modal-body">
												<div class="form-group"><label>Name</label> <input type="text"
														name="name" value="{{ $a->name }}" class="form-control"
														required></div>
											</div>
											<div class="modal-body no-padding">
												<textarea class="form-control summernote"
													name="content">{!! $a->content !!}</textarea>
											</div>
											<div class="modal-footer">
												<button type="submit" class="btn btn-primary">Save changes</button>
											</div>
										</form>
									</div>
								</div>
							</div>
							<div class="modal inmodal" id="delete{{ $a->id }}" tabindex="-1" role="dialog"
								aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content animated fadeInDown">
										<form action="{{ url('a/page/'.$a->id) }}" method="POST">
											@csrf
											{{ method_field('DELETE') }}
											<div class="modal-header">
												<button type="button" class="close" data-dismiss="modal"><span
														aria-hidden="true">&times;</span><span
														class="sr-only">Close</span></button>
												<i class="fa fa-trash modal-icon"></i>
											</div>
											<div class="modal-body">
												Are you sure want to delete this data?
											</div>
											<div class="modal-footer">
												<button type="submit" class="btn btn-danger">Delete</button>
											</div>
										</form>
									</div>
								</div>
							</div>
						</td>
					</tr>
					@endforeach
				</tbody>
				<tfoot>
					<tr>
						<td colspan="5">
							<ul class="pagination pull-right"></ul>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
@endsection
@section('script')
<!-- SUMMERNOTE -->
<script src="{{ asset('inspinia/js/plugins/summernote/summernote.min.js') }}"></script>
<!-- FooTable -->
<script src="{{ asset('inspinia/js/plugins/footable/footable.all.min.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('.summernote').summernote();
		$('.footable').footable();
    });
</script>
@endsection