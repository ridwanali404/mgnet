@extends('marketplace.layouts.admin')
@section('title')
Product
@endsection
@section('style')
<!-- FooTable -->
<link href="{{ asset('inspinia/css/plugins/footable/footable.core.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="ibox">
	<div class="ibox-title">
		<h5>Product</h5>
		<div class="ibox-tools">
			<a href="{{ url('a/product/create') }}">
				<i class="fa fa-plus"></i>
				<span>New Product</span>
			</a>
		</div>
	</div>
	<div class="ibox-content">
		<input type="text" class="form-control input-sm m-b-xs" id="filter" placeholder="Search in table">
		<div class="table-responsive">
			<table class="footable table table-stripped toggle-arrow-tiny" data-page-size="8" data-filter=#filter>
				<thead>
					<tr>
						<th data-toggle="true">Name</th>
						<th>Category</th>
						<th>Price</th>
						<th>Last Update</th>
						<th data-hide="all">Desc</th>
						<th data-hide="all">Quantity</th>
						<th data-hide="all">Sold out</th>
						<th class="text-right" data-sort-ignore="true">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($products as $a)
					<tr>
						<td>{{ $a->name }}</td>
						<td>{{ $a->category->name }}</td>
						<td>Rp {{ number_format($a->price) }}</td>
						<td>{{ $a->updated_at }}</td>
						<td>{!! $a->content !!}</td>
						<td>{{ $a->qty }}</td>
						<td>{{ $a->sold }}</td>
						<td>
							<div class="btn-group pull-right">
								<a href="{{ url('a/product/'.$a->id.'/edit') }}" class="btn-white btn btn-xs">Edit</a>
								<button class="btn-white btn btn-xs" data-toggle="modal"
									data-target="#delete{{ $a->id }}">Delete</button>
							</div>
							<div class="modal inmodal" id="delete{{ $a->id }}" tabindex="-1" role="dialog"
								aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content animated fadeInDown">
										<form action="{{ url('a/product/'.$a->id) }}" method="POST">
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
<!-- FooTable -->
<script src="{{ asset('inspinia/js/plugins/footable/footable.all.min.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('.footable').footable();
    });
</script>
@endsection