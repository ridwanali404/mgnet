@extends('marketplace.layouts.admin')
@section('title')
Blog
@endsection
@section('style')
<!-- SUMMERNOTE -->
<link href="{{ asset('inspinia/css/plugins/summernote/summernote.css') }}" rel="stylesheet">
<link href="{{ asset('inspinia/css/plugins/summernote/summernote-bs3.css') }}" rel="stylesheet">
<!-- FooTable -->
<link href="{{ asset('inspinia/css/plugins/footable/footable.core.css') }}" rel="stylesheet">
<link href="{{ asset('bootstrap-imageupload/dist/css/bootstrap-imageupload.min.css') }}" rel="stylesheet">
<style>
	a {
		outline: none !important;
	}
</style>
@endsection
@section('content')
<div class="ibox float-e-margins">
	<form action="{{ url('a/blog') }}" method="POST" enctype="multipart/form-data" onsubmit="add.disabled = true;">
		@csrf
		<div class="ibox-title">
			<h5>Content</h5>
			<div class="ibox-tools">
				<button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#add">
					<span>Post</span>
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
							<label>Title</label>
							<input type="text" name="title" class="form-control" required>
						</div>
						<div class="imageupload panel panel-default">
							<div class="panel-heading clearfix">
								<h3 class="panel-title pull-left">Gallery Image</h3>
								<div class="btn-group pull-right">
									<button type="button" class="btn btn-default active">File</button>
									<button type="button" class="btn btn-default">URL</button>
								</div>
							</div>
							<div class="file-tab panel-body">
								<label class="btn btn-default btn-file">
									<span>Browse</span>
									<!-- The file is stored here. -->
									<input type="file" name="image">
								</label>
								<button type="button" class="btn btn-default">Remove</button>
							</div>
							<div class="url-tab panel-body">
								<div class="input-group">
									<input type="text" class="form-control" placeholder="Image URL">
									<div class="input-group-btn">
										<button type="button" class="btn btn-default">Submit</button>
									</div>
								</div>
								<button type="button" class="btn btn-default">Remove</button>
								<!-- The URL is stored here. -->
								<input type="hidden" name="image-url">
							</div>
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
		<h5>Blog</h5>
	</div>
	<div class="ibox-content">
		<input type="text" class="form-control input-sm m-b-xs" id="filter" placeholder="Search in table">
		<div class="table-responsive">
			<table class="footable table table-stripped toggle-arrow-tiny" data-page-size="8" data-filter=#filter>
				<thead>
					<tr>
						<th data-toggle="true">Title</th>
						<th>Image</th>
						<th>Date</th>
						<th data-hide="all">Content</th>
						<th class="text-right" data-sort-ignore="true">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($blogs as $a)
					<tr>
						<td>{{ $a->title }}</td>
						<td>
							@if($a->image)
							<a href="#" type="button" data-toggle="modal" data-target=".image{{$a->id}}">View image</a>
							<div class="modal inmodal image{{$a->id}}" role="dialog">
								<div class="modal-dialog modal-lg">
									<div class="modal-content animated fadeInDown">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal"><span
													aria-hidden="true">&times;</span><span
													class="sr-only">Close</span></button>
											<i class="fa fa-picture-o modal-icon"></i>
										</div>
										<div class="modal-body">
											<center>
												<img src="{{ asset($a->image_path) }}" class="img-thumbnail" />
											</center>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-danger"
												data-dismiss="modal">Close</button>
										</div>
									</div>
								</div>
							</div>
							@else
							No image
							@endif
						</td>
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
										<form action="{{ url('a/blog/'.$a->id) }}" method="POST"
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
												<div class="form-group"><label>Title</label> <input type="text"
														name="title" value="{{ $a->title }}" class="form-control"
														required></div>
												<div class="imageupload panel panel-default">
													<div class="panel-heading clearfix">
														<h3 class="panel-title pull-left">Gallery Image</h3>
														<div class="btn-group pull-right">
															<button type="button"
																class="btn btn-default active">File</button>
															<button type="button" class="btn btn-default">URL</button>
														</div>
													</div>
													<div class="file-tab panel-body">
														<label class="btn btn-default btn-file">
															<span>Browse</span>
															<!-- The file is stored here. -->
															<input type="file" name="image">
														</label>
														<button type="button" class="btn btn-default">Remove</button>
													</div>
													<div class="url-tab panel-body">
														<div class="input-group">
															<input type="text" class="form-control"
																placeholder="Image URL">
															<div class="input-group-btn">
																<button type="button"
																	class="btn btn-default">Submit</button>
															</div>
														</div>
														<button type="button" class="btn btn-default">Remove</button>
														<!-- The URL is stored here. -->
														<input type="hidden" name="image-url">
													</div>
												</div>
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
										<form action="{{ url('a/blog/'.$a->id) }}" method="POST">
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
<script src="{{ asset('bootstrap-imageupload/dist/js/bootstrap-imageupload.min.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('.summernote').summernote();
		$('.footable').footable();
		$('.imageupload').imageupload({
			maxFileSizeKb: 512
		});
    });
</script>
@endsection