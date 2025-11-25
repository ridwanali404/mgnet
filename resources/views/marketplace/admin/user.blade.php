@extends('marketplace.layouts.admin')
@section('title')
Users
@endsection
@section('style')
<!-- FooTable -->
<link href="{{ asset('inspinia/css/plugins/footable/footable.core.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="ibox">
	<div class="ibox-title">
		<h5>Users</h5>
	</div>
	<div class="ibox-content">
		<input type="text" class="form-control input-sm m-b-xs" id="filter" placeholder="Search in table">
		<div class="table-responsive">
			<table class="footable table table-stripped toggle-arrow-tiny" data-page-size="8" data-filter=#filter>
				<thead>
					<tr>
						<th data-toggle="true">Username</th>
						<th>Status</th>
						<th data-hide="all">Email</th>
						<th data-hide="all">Phone</th>
						<th data-hide="all">Image</th>
						<th class="text-right" data-sort-ignore="true">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($users as $a)
					<tr>
						<td>{{ $a->username }}</td>
						<td><span
								class="label label-{{ $a->type == 'admin' ? 'danger' : 'primary' }}">{{ $a->type == 'admin' ? 'Admin' : 'Member' }}</span>
						</td>
						<td>{{ $a->email }}</td>
						<td>{{ $a->phone ? '+62'.$a->phone : 'No Phone' }}</td>
						<td>
							@if($a->image)
							<img class="img-thumbnail" href="{{ asset($a->image) }}" />
							@else
							No Image
							@endif
						</td>
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
										<form action="{{ url('a/user/'.$a->id) }}" method="POST"
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
										<form action="{{ url('a/user/'.$a->id) }}" method="POST">
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
@endsection
@section('script')
<!-- FooTable -->
<script src="{{ asset('inspinia/js/plugins/footable/footable.all.min.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('.footable').footable();
    });
</script>
@endsection