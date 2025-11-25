@extends('marketplace.layouts.admin')
@section('title', 'Transaksi Member')
@section('style')
<link href="{{ asset('inspinia/css/plugins/dataTables/datatables.min.css') }}" rel="stylesheet">
<style>
	a {
		outline: none !important;
	}
</style>
@endsection
@section('content')
<div class="ibox">
	<div class="ibox-title">
		<h5>
			Transaksi Member
		</h5>
	</div>
	<div class="ibox-content">
        <form action="{{ route('admin.transaction.general') }}" method="get">
			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						<label class="control-label" for="month">Bulan</label>
						<input type="month" id="month" name="month" value="{{ request()->month ?? date('Y-m') }}" class="form-control">
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						<label class="control-label" for="username">Username Member</label>
						<input type="text" id="username" name="username" value="{{ request()->username }}" class="form-control">
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						<label class="control-label" for="status">Status</label>
						<select name="status" id="status" class="form-control">
							<option value="all">Semua Status</option>
							<option value="pending" {{ request()->status == 'pending' ? 'selected' : '' }}>Menunggu pembayaran</option>
							<option value="expired" {{ request()->status == 'expired' ? 'selected' : '' }}>Kadaluarsa</option>
							<option value="paid" {{ request()->status == 'paid' ? 'selected' : '' }}>Sudah dibayar</option>
							<option value="packed" {{ request()->status == 'packed' ? 'selected' : '' }}>Sudah dikemas</option>
							<option value="shipped" {{ request()->status == 'shipped' ? 'selected' : '' }}>Sedang dikirim</option>
							<option value="received" {{ request()->status == 'received' ? 'selected' : '' }}>Diterima</option>
						</select>
					</div>
				</div>
			</div>
			<button class="btn btn-primary" type="submit">Filter</button>
		</form>
        <div class="hr-line-dashed"></div>
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-hover transactions">
				<thead>
					<tr>
						<th>TRO ID</th>
						<th>Tanggal</th>
						<th>Status</th>
						<th>Username</th>
						<th>Nama</th>
						<th>Nomor HP</th>
						<th>Nama Sponsor</th>
						<th>Username Sponsor</th>
						<th>Alamat</th>
						<th>Kecamatan</th>
						<th>Kota/Kabupaten</th>
						<th>Provinsi</th>
						<th>Kode Pos</th>
						<th>Rincian Barang</th>
						<th>Kurir</th>
						<th>Biaya Ongkir</th>
						<th>Nomor Resi Pengiriman</th>
						<th>Kode Unik</th>
						<th>Jumlah Transfer</th>
						<th>Struk Pembayaran</th>
						<th data-orderable="false">Aksi</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($transactions as $transaction)
					<tr class="gradeX">
						<td><code>{{ \Carbon\Carbon::parse($transaction->created_at)->format('YmdHis') }}</code></td>
						<td><code>{{ $transaction->created_at }}</code></td>
						<td>
							@if($transaction->status == 'pending')
							<span class="label label-warning">Menunggu pembayaran</span>
							@elseif($transaction->status == 'expired')
							<span class="label label-danger">Kadaluarsa</span>
							@elseif($transaction->status == 'paid')
							<span class="label label-success">Sudah dibayar</span>
							@elseif($transaction->status == 'packed')
							<span class="label label-success">Sudah dikemas</span>
							@elseif($transaction->status == 'shipped')
							<span class="label label-info">Sedang dikirim</span>
							@elseif($transaction->status == 'received')
							<span class="label label-primary">Diterima</span>
							@endif
						</td>
						<td>{{ $transaction->user->username ?? '' }}</td>
						<td>{{ $transaction->address->recipient ?? ($transaction->user->name ?? '') }}</td>
						<td>{{ $transaction->address->phone ?? ($transaction->user->phone ?? '') }}</td>
						<td>{{ $transaction->sponsor->name ?? ($transaction->user ? ($transaction->user->sponsor->name ?? '') : '') }}</td>
						<td>{{ $transaction->sponsor->username ?? ($transaction->user ? ($transaction->user->sponsor->username ?? '') : '') }}</td>
						<td>{{ $transaction->address->address ?? '' }}</td>
						<td>{{ $transaction->address->subdistrict->subdistrict_name ?? '' }}</td>
						<td>{{ $transaction->address->city->city_name ?? '' }}</td>
						<td>{{ $transaction->address->province->province ?? '' }}</td>
						<td>{{ $transaction->address->postal_code ?? '' }}</td>
						<td>
							@foreach($transaction->carts as $cart)
							<div class="text-nowrap"><a href="{{ url('product/'.$cart->product->dash_name) }}" target="_blank">{{ $cart->name }}</a>, <b>Qty</b> {{ $cart->qty }}
								pcs. <b>Harga</b> Rp&nbsp;{{ number_format($cart->price) }}. <b>Total</b>
								Rp&nbsp;{{ number_format($cart->price_total) }}.</div>
							@endforeach
						</td>
						<td>{{ $transaction->shipment }}</td>
						<td>Rp&nbsp;{{ number_format($transaction->shipment_fee) }}</td>
						<td>{{ $transaction->shipment_number ?? '' }}</td>
						<td>Rp&nbsp;{{ number_format($transaction->code) }}</td>
						<td>Rp&nbsp;{{ number_format($transaction->price_total) }}</td>
						<td>
							@if($transaction->receipt)
							<a href="#" type="button" data-toggle="modal" data-target=".image{{$transaction->id}}">View
								receipt</a>
							@endif
						</td>
						<td class="text-nowrap text-right">
							@if($transaction->status == 'pending')
							@if(Auth::user()->type == 'admin' || Auth::user()->is_master_stockist || (Auth::user()->type == 'cradmin' && in_array('Keuangan', Auth::user()->roles ?? [])))
							<button class="btn-white btn btn-xs" data-toggle="modal"
								data-target="#confirm{{ $transaction->id }}">Konfirmasi pembayaran</button>
							@endif
							@elseif($transaction->status == 'paid')
							@if(Auth::user()->type == 'admin' || Auth::user()->is_master_stockist || (Auth::user()->type == 'cradmin' && in_array('Gudang', Auth::user()->roles ?? [])))
							<button class="btn-white btn btn-xs" data-toggle="modal"
								data-target="#packed{{ $transaction->id }}">Konfirmasi sudah dikemas</button>
							@endif
							@elseif(in_array($transaction->status, ['packed', 'shipped']))
							@if(Auth::user()->type == 'admin' || Auth::user()->is_master_stockist || (Auth::user()->type == 'cradmin' && in_array('Ekspedisi', Auth::user()->roles ?? [])))
							<button class="btn-white btn btn-xs" data-toggle="modal"
								data-target="#shipment{{ $transaction->id }}">Resi pengiriman</button>
							@endif
							@endif
							@if(false)
							<button class="btn-white btn btn-xs" data-toggle="modal"
								data-target="#delete{{ $transaction->id }}">Hapus</button>
							@endif
						</td>
					</tr>
					@endforeach
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
				<button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
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
<div class="modal inmodal" id="packed{{ $transaction->id }}" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content animated fadeInDown">
			<form action="{{ url('a/transaction/'.$transaction->id.'/packed') }}" method="POST">
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
@elseif(in_array($transaction->status, ['packed', 'shipped']))
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
						<input type="text" name="shipment_number" value="{{ $transaction->shipment_number }}" class="form-control">
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
@if(false)
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
					Apakah anda yakin akan menghapus transaksi ini?
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger">Hapus</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endif
@endforeach
@endsection
@section('script')
<!-- FooTable -->
<script src="{{ asset('inspinia/js/plugins/dataTables/datatables.min.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('.transactions').DataTable({
			order: [[ 1, "desc" ]],
			pageLength: 25,
			responsive: true,
			dom: '<"html5buttons"B>lTfgitp',
			buttons: [
				{extend: 'copy'},
				{extend: 'csv'},
				{extend: 'excel', title: 'Transaksi'},
				{extend: 'pdf', title: 'Transaksi'},
				{extend: 'print',
					customize: function (win){
						$(win.document.body).addClass('white-bg');
						$(win.document.body).css('font-size', '10px');
						$(win.document.body).find('table')
							.addClass('compact')
							.css('font-size', 'inherit');
					}
				}
			],
			language: {
				url: "//cdn.datatables.net/plug-ins/1.11.3/i18n/id.json"
			}
		});
    });
</script>
@endsection