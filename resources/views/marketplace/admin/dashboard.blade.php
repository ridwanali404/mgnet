@extends('marketplace.layouts.admin')
@section('title')
Dashboard
@endsection
@section('style')
<!-- FooTable -->
<link href="{{ asset('inspinia/css/plugins/footable/footable.core.css') }}" rel="stylesheet">
<style>
	a {
		outline: none !important;
	}
</style>
@endsection
@section('content')
<div class="ibox">
	<div class="ibox-title">
		<h5>Transaction</h5>
	</div>
	<div class="ibox-content">
		<input type="text" class="form-control input-sm m-b-xs" id="filter" placeholder="Search in table">
		<div class="table-responsive">
			<table class="footable table table-stripped toggle-arrow-tiny" data-page-size="8" data-filter=#filter>
				<thead>
					<tr>
						<th data-toggle="true">Date</th>
						<th>Name</th>
						<th>Address</th>
						<th>Total</th>
						<th>Receipt</th>
						<th>Last Update</th>
						<th>Status</th>
						<th data-hide="all">Shopping List</th>
						<th data-hide="all">Courier</th>
						<th data-hide="all">Courier Cost</th>
						<th data-hide="all">Shipment Number</th>
						<th class="text-right" data-sort-ignore="true">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($transactions as $transaction)
					<tr>
						<td>{{ $transaction->created_at }}</td>
						<td>
							@if($transaction->user)
							<a href="{{ url('user/'.$transaction->user->id.'/profile') }}">
								{{ $transaction->user->username }}
							</a>
							@else
							-
							@endif
						</td>
						<td>
							@if($transaction->address)
							{{ $transaction->address->name }}<br />
							<small>{{ $transaction->address->phone ? '+62'.$transaction->address->phone : 'No phone'}}</small><br />
							<small>{!! $transaction->address->address !!}</small><br />
							<small>{{ $transaction->address->subdistrict->subdistrict_name.', '.$transaction->address->city->city_name.', '.$transaction->address->province->province.' '.$transaction->address->postal_code.'.' }}</small>
							@else
							-
							@endif
						</td>
						<td>Rp {{ number_format($transaction->price_total) }}</td>
						<td>
							@if($transaction->receipt)
							<a href="#" type="button" data-toggle="modal" data-target=".image{{$transaction->id}}">View
								receipt</a>
							@endif
						</td>
						<td>{{ $transaction->updated_at }}</td>
						<td>
							@if($transaction->status == 'pending')
							<span class="label label-warning">Menunggu pembayaran</span>
							@elseif($transaction->status == 'expired')
							<span class="label label-danger">Kadaluarsa</span>
							@elseif($transaction->status == 'paid')
							<span class="label label-success">Sudah dibayar</span>
							@elseif($transaction->status == 'shipped')
							<span class="label label-info">Sedang dikirim</span>
							@elseif($transaction->status == 'received')
							<span class="label label-primary">Diterima</span>
							@endif
						</td>
						<td>
							@foreach($transaction->carts as $cart)
							<div><a href="{{ url('product/'.$cart->product->dash_name) }}"
									target="_blank">{{ $cart->name }}</a>, <b>Qty</b> {{ $cart->qty }}
								pcs. <b>Price</b> Rp&nbsp;{{ number_format($cart->price) }}. <b>Total</b>
								Rp&nbsp;{{ number_format($cart->price_total) }}.</div>
							@endforeach
						</td>
						<td>{{ $transaction->shipment }}</td>
						<td>Rp&nbsp;{{ number_format($transaction->shipment_fee) }}</td>
						<td>{{ $transaction->shipment_number ?? '-' }}</td>
						<td>
							<div class="btn-group pull-right">
								@if($transaction->status == 'pending')
								<button class="btn-white btn btn-xs" data-toggle="modal"
									data-target="#confirm{{ $transaction->id }}">Confirm payment</button>
								@elseif($transaction->status == 'paid')
								<button class="btn-white btn btn-xs" data-toggle="modal"
									data-target="#shipment{{ $transaction->id }}">Input shipment receipt</button>
								@endif
								<button class="btn-white btn btn-xs" data-toggle="modal"
									data-target="#delete{{ $transaction->id }}">Delete</button>
							</div>
						</td>
					</tr>
					@endforeach
				</tbody>
				<tfoot>
					<tr>
						<td colspan="8">
							<ul class="pagination pull-right"></ul>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
@foreach ($transactions as $transaction)
@if($transaction->receipt)
<div class="modal inmodal image{{$transaction->id}}" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content animated fadeInDown">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
						class="sr-only">Close</span></button>
				<i class="fa fa-picture-o modal-icon"></i>
			</div>
			<div class="modal-body">
				<center>
					<img src="{{ asset($transaction->receipt) }}" class="img-thumbnail" />
				</center>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
@endif
@if($transaction->status == 'pending')
<div class="modal inmodal" id="confirm{{ $transaction->id }}" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content animated fadeInDown">
			<form action="{{ url('a/transaction/'.$transaction->id.'/confirm') }}" method="POST">
				@csrf
				{{ method_field('PUT') }}
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span
							aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<i class="fa fa-check modal-icon"></i>
				</div>
				<div class="modal-body">
					Are you sure want to confirm this data?
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info">Confirm</button>
				</div>
			</form>
		</div>
	</div>
</div>
@elseif($transaction->status == 'paid')
<div class="modal inmodal" id="shipment{{ $transaction->id }}" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content animated fadeInDown">
			<form action="{{ url('a/transaction/'.$transaction->id.'/shipment') }}" method="POST">
				@csrf
				{{ method_field('PUT') }}
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span
							aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<i class="fa fa-truck modal-icon"></i>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>No. Resi</label>
						<input type="text" name="shipment_number" class="form-control">
						<span class="form-text m-b-none text-muted">{{ $transaction->shipment }}.</span>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info">Confirm shipment</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endif
<div class="modal inmodal" id="delete{{ $transaction->id }}" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content animated fadeInDown">
			<form action="{{ url('a/transaction/'.$transaction->id) }}" method="POST">
				@csrf
				{{ method_field('DELETE') }}
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span
							aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
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
@endforeach
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