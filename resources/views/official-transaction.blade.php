@extends('layout.app')
@section('title', 'Transaksi Produk RO')
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
			<h3 class="text-themecolor m-b-0 m-t-0">Transaksi Produk RO</h3>
			<ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="#">Home</a>
				</li>
				<li class="breadcrumb-item active">Transaksi Produk RO</li>
			</ol>
		</div>
		<div class="col-md-7 col-4 align-self-center">
			<div class="d-flex m-t-10 justify-content-end">
				@if(Auth::user()->type == 'admin')
				<a href="#" class="btn btn-rounded waves-effect waves-light btn-primary pull-right" data-toggle="modal"
					data-target=".add-stockist"> <i class="fas fa-users"></i><span class="d-none d-md-inline">
						&nbsp;Buat Transaksi Stokis</span></a> &nbsp;
				@endif
				@if(Auth::user()->type == 'admin' || Auth::user()->is_stockist || Auth::user()->is_master_stockist)
				<a href="#" class="btn btn-rounded waves-effect waves-light btn-danger pull-right" data-toggle="modal"
					data-target=".add"> <i class="fas fa-plus-circle"></i><span class="d-none d-md-inline">
						&nbsp;Buat Transaksi</span></a> &nbsp;
				@endif
			</div>
		</div>
	</div>
	<form class="form-group" id="filter-month" method="GET" action="{{ url('official-transaction') }}">
		<input class="form-control" type="month" name="month" value="{{ request()->get('month') ?? date('Y-m') }}"
			id="month">
	</form>

	@if(Auth::user()->is_stockist || Auth::user()->is_master_stockist)
	<div class="card">
		<div class="card-body table-responsive p-0">
			<table class="table table-hover m-b-0">
				<thead>
					<tr>
						<th colspan="2">
							<h4 class="m-b-0">Data Stok Stokis</h4>
						</th>
					</tr>
				</thead>
				@foreach(Auth::user()->officialTransactionStockists()->where('current', '!=', 0)->groupBy('product_id')->get() as $a)
				<tr>
					<td>
						{{ $a->product->name }}
					</td>
					<td class="text-right">
						<code>{{ number_format(Auth::user()->officialTransactionStockists()->where('product_id', $a->product->id)->sum('current')) }}</code>
					</td>
				</tr>
				@endforeach
			</table>
		</div>
	</div>
	@endif

	@if(Auth::user()->type == 'admin' || Auth::user()->is_stockist || Auth::user()->is_master_stockist)
	<div class="card">
		<div class="card-body">
			<h3 class="card-title">Transaksi Produk RO Stokis</h3>
			<div class="table-responsive">
				<table id="admin-official-transaction"
					class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>#</th>
							<th>Dibuat pada</th>
							@if(Auth::user()->type == 'admin')
							<th>Stokis</th>
							@endif
							<th>Produk</th>
							<th>Jumlah</th>
							<th>Total Harga</th>
							<th>Poin</th>
						</tr>
					</thead>
					<tbody>
						@foreach($official_transaction_stockists as $a)
						<tr>
							<td>{{ $loop->index + 1 }}</td>
							<td>
								<code>{{ $a->created_at }}</code>
							</td>
							@if(Auth::user()->type == 'admin')
							<td>{{ $a->user->username }}</td>
							@endif
							<td>{{ $a->product->name }}</td>
							<td class="text-right">{{ number_format($a->qty) }}</td>
							<td class="text-right">{{ number_format($a->price) }}</td>
							<td class="text-right">{{ number_format($a->poin) }}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
	@endif

	@if(Auth::user()->is_stockist || Auth::user()->is_master_stockist)
	<div class="card">
		<div class="card-body">
			<h3 class="card-title">Transaksi Produk RO oleh Stokis</h3>
			<div class="table-responsive">
				<table id="my-official-transaction"
					class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>#</th>
							<th>Dibuat pada</th>
							<th>Member</th>
							<th>Produk</th>
							<th>Jumlah</th>
							<th>Total Harga</th>
							<th>Poin</th>
							@if(Auth::user()->type == 'admin')
							<th>Aksi</th>
							@endif
						</tr>
					</thead>
					<tbody>
						@foreach(Auth::user()->monthlyStockistOfficialTransactions(request()->get('month') ?? date('Y-m'))->latest()->get() as $a)
						<tr>
							<td>{{ $loop->index + 1 }}</td>
							<td>
								<code>{{ $a->created_at }}</code>
								@if($a->is_topup)
								<label class="label label-rounded label-success m-l-5 m-b-0">Topup</label>
								@endif
							</td>
							<td>{{ $a->user->username }}</td>
							<td>{{ $a->product->name }}</td>
							<td class="text-right">{{ number_format($a->qty) }}</td>
							<td class="text-right">{{ number_format($a->price) }}</td>
							<td class="text-right">{{ number_format($a->poin) }}</td>
							@if(Auth::user()->type == 'admin')
							<td class="text-nowrap text-right">
								<a href="#" data-toggle="modal" data-target=".update-{{ $a->id }}"><i
										class="mdi mdi-pencil text-inverse m-r-10"></i> </a>
							</td>
							@endif
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
	@endif

	@if($official_transactions->count())
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-body">
					@if(Auth::user()->type == 'admin')
					<a href="{{ route('admin.transaction.official') }}" class="float-right btn btn-sm btn-rounded btn-danger">Kelola Transkasi Perusahaan</a>
					@endif
					<h3 class="card-title">Transaksi Produk RO</h3>
					<div class="table-responsive">
						<table id="official-transaction"
							class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
							width="100%">
							<thead>
								<tr>
									<th>#</th>
									<th>Dibuat pada</th>
									<th>Status</th>
									<th>Dibuat oleh</th>
									<th>Member</th>
									<th>Produk</th>
									<th>Jumlah</th>
									<th>Total Harga</th>
									<th>Poin</th>
									@if(Auth::user()->type == 'admin')
									<th>Aksi</th>
									@endif
								</tr>
							</thead>
							<tbody>
								@foreach ($official_transactions as $a)
								<tr>
									<td>{{ $loop->index + 1 }}</td>
									<td>
										<code>{{ $a->created_at }}</code>
										@if($a->is_topup)
										<label class="label label-rounded label-success m-l-5 m-b-0">Topup</label>
										@endif
									</td>
									<td>
										@if($a->status == 'pending')
										<span class="label label-warning">Menunggu pembayaran</span>
										@elseif($a->status == 'expired')
										<span class="label label-danger">Kadaluarsa</span>
										@elseif($a->status == 'paid')
										<span class="label label-success">Sudah dibayar</span>
										@elseif($a->status == 'packed')
										<span class="label label-success">Sudah dikemas</span>
										@elseif($a->status == 'shipped')
										<span class="label label-info">Sedang dikirim</span>
										@elseif($a->status == 'received')
										<span class="label label-primary">Diterima</span>
										@endif
									</td>
									<td>
										{{ $a->stockist_id ? $a->stockist->username : 'Perusahaan' }}
										@if($a->stockist_id)
										<label class="label label-rounded label-success m-l-5 m-b-0">Stokis</label>
										@endif
									</td>
									<td>{{ $a->user->username }}</td>
									<td>
										{{ $a->product_name ?? ($a->product->name ?? '') }} {{ $a->is_turbo ? '(Paket Turbo)' : '' }} {{ $a->is_big ? '(Paket Besar)' : '' }}
										@if($a->month_key > 1)
										<label class="label label-rounded label-success m-l-5 m-b-0">Bulan ke {{ $a->month_key }}</label>
										@endif
									</td>
									<td class="text-right">{{ number_format($a->qty) }}</td>
									<td class="text-right">{{ number_format($a->price) }}</td>
									<td class="text-right">{{ number_format($a->poin) }}</td>
									@if(Auth::user()->type == 'admin')
									<td class="text-nowrap text-right">
										{{-- <a href="#" data-toggle="modal" data-target=".update-{{ $a->id }}"><i
												class="mdi mdi-pencil text-inverse m-r-10"></i> </a> --}}
										@if(!$a->stockist_id && !($a->month_key > 1))
										<a href="#" data-toggle="modal" data-target=".delete-{{ $a->id }}"><i class="mdi mdi-delete text-danger"></i></a>
										@endif
									</td>
									@endif
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	@endif
	<div class="modal fade add-stockist" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content animated fadeInDown">
				<form action="{{ route('official-transaction-stockist.store') }}" method="POST"
					onsubmit="add.disabled = true;">
					@csrf
					<div class="modal-header">
						<h4 class="modal-title" id="myModalLabel">Buat Transaksi Stokis</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label>Produk</label>
							<select class="product-official-select2" style="width: 100%;" name="product_id"
								placeholder="Cari produk RO..." required></select>
						</div>
						<div class="form-group">
							<label>Stokis</label>
							<select id="stockist" style="width: 100%;" name="user_id" placeholder="Cari stokis..."
								required></select>
						</div>
						<div class="form-group">
							<label>Jumlah</label>
							<input type="number" class="form-control" min="1" name="qty" required>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" name="add" class="btn btn-info">Buat</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="modal fade add" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content animated fadeInDown">
				<form action="{{ url('official-transaction') }}" method="POST" onsubmit="add.disabled = true;">
					@csrf
					@if(Auth::user()->type == 'admin')
					<input type="hidden" id="courier_text" name="courier_text">
					@endif
					<div class="modal-header">
						<h4 class="modal-title" id="myModalLabel">Buat Transaksi</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label>Produk</label>
							<select class="product-official-big-select2" style="width: 100%;" name="product_id"
								placeholder="Cari produk RO..." required></select>
						</div>
						<div class="form-group">
							<label>Member</label>
							<select id="member" style="width: 100%;" name="member_id" placeholder="Cari member..."
								required></select>
						</div>
						@if(Auth::user()->type == 'admin')
						<div id="address"></div>
						@endif
						<div class="form-group">
							<label>Jumlah</label>
							<input type="number" class="form-control" min="1" name="qty" id="qty" onchange="findCourier()" required>
						</div>
						@if(Auth::user()->type == 'admin')
						<div id="big"></div>
						<div class="form-group">
							<label>Pilih Kurir <div id="courier-loading" class="spinner-border spinner-border-sm" role="status" style="margin-bottom: 2px"><span class="sr-only">Loading...</span></div></label>
							<select id="courier_cost" name="courier_cost" class="form-control sm-form-control" required>
								<option selected disabled>Pilih kurir</option>
								<option>COD</option>
								<option>Ambil di Kantor</option>
							</select>
						</div>
						<div>
							<input type="checkbox" name="is_topup" id="is_topup_checkbox">
							<label for="is_topup_checkbox">Topup</label>
						</div>
						<span class="help-block text-muted">
							<small>Centang apabila transaksi yang dilakukan adalah
								Topup.</small>
						</span>
						@if(false)
						<div class="form-group">
							<label>Total</label>
							<input type="number" class="form-control" name="price_total" id="price_total" readonly required>
						</div>
						@endif
						@endif
					</div>
					<div class="modal-footer">
						<button type="submit" name="add" class="btn btn-info">Buat</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@foreach ($official_transactions as $a)
	{{-- urgent! need to check if month closing is already generated --}}
	@if(false)
	<div class="modal fade update-{{ $a->id }}" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content animated fadeInDown">
				<form action="{{ url('official-transaction/'.$a->id) }}" method="POST" onsubmit="add.disabled = true;">
					@csrf
					@method('put')
					<div class="modal-header">
						<h4 class="modal-title" id="myModalLabel">Edit Transaksi</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label>Jumlah</label>
							<input type="number" class="form-control" name="qty" value="{{ $a->qty }}" required>
						</div>
						<div class="form-group">
							<label>Poin</label>
							<input type="number" class="form-control" name="poin" value="{{ $a->poin }}" required>
						</div>
					</div>
					<div class="modal-footer"> 
						<button type="submit" name="add" class="btn btn-info">Simpan</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endif
	@if(!$a->stockist_id && !($a->month_key > 1))
	<div class="modal fade delete-{{ $a->id }}">
		<div class="modal-dialog">
			<div class="modal-content">
				<form class="form-material" action="{{ url('official-transaction/'.$a->id) }}" method="POST">
					@csrf
					@method('delete')
					<div class="modal-body">
						<h4>Hapus</h4>
						<p>Apakah Anda yakin?</p>
						<p class="text-right">
							<button type="submit" class="btn btn-danger waves-effect">Hapus</button>
						</p>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endif
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
	function getAddress(address) {
		$.get('/address/' + address, function(data, status){
			if (status == 'success') {
				$("#address-detail").html(`
					<span class="help-block text-muted">
						<p style="font-weight: 400; font-size: 12px; margin-top: .6em;">
							Nama Penerima <strong>` + (data.recipient ?? '') + `</strong><br>
							Email <strong>` + (data.email ?? '') + `</strong><br>
							Nomor HP <strong>` + (data.phone ?? '') + `</strong><br>
							Alamat <strong>` + (data.address ?? '') + `</strong><br>
							Provinsi <strong>` + (data.province.province ?? '') + `</strong><br>
							Kabupaten/Kota <strong>` + (data.city.city_name ?? '') + `</strong><br>
							Kecamatan <strong>` + (data.subdistrict.subdistrict_name ?? '') + `</strong><br>
							Kode Pos <strong>` + (data.postal_code ?? '') + `</strong>
						</p>
					</span>
				`);
				findCourier();
			}
		});
	}
    function findCourier() {
		if ($("#qty").val() && $("#address_id").find(":selected").val() && $(".product-official-big-select2").find(":selected").val()) {
			$('#courier-loading').show();
			$('#courier_cost').html('<option selected disabled>Pilih Kurir</option><option>COD</option><option>Ambil di Kantor</option>');
			var input = {
				_token: '{{ csrf_token() }}',
				address_id: $("#address_id").find(":selected").val(),
				product_id: $(".product-official-big-select2").find(":selected").val(),
				qty: $("#qty").val(),
				qty_month: $("#qty_month").val(),
			};
			$.post('{{ url("courier-official") }}', input, function(data) {
				$('#courier-loading').hide();
				data = JSON.parse(data);
				$.each(data.rajaongkir.results, function(i, result) {
					$.each(result.costs, function(j, cost) {
						$('#courier_cost').append($('<option>').text(result.name + ' ' + cost.service + ' ' + cost.cost[0].value + ' ' + cost.cost[0].etd).attr('value', cost.cost[0].value));
					});
				});
			});
		}
    }
	jQuery(document).ready(function () {
		$('#courier-loading').hide();
		$('#official-transaction').DataTable({
			"language": {
				"url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
			},
			"order": [[1, "desc"]]
		});
		$('#my-official-transaction').DataTable({
			"language": {
				"url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
			},
			"order": [[1, "desc"]]
		});
		$('#admin-official-transaction').DataTable({
			"language": {
				"url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
			},
			"order": [[1, "desc"]]
		});
		$(".product-official-select2").select2({
			placeholder: "Cari produk...",
			ajax: {
				url: '/official-product',
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
		$(".product-official-big-select2").select2({
			placeholder: "Cari produk...",
			ajax: {
				url: '/official-product-big',
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
		}).on('select2:select', function (e) {
			if (e.params.data.is_big) {
				$('#big').html(`
					<div class="form-group">
						<label>Jumlah Bulan</label>
						<input type="number" class="form-control" name="month" id="qty_month" value="` + e.params.data.month + `" readonly required>
					</div>
				`);
			} else {
				$('#big').html('');
			}
		});
		$("#member").select2({
			placeholder: "Cari member...",
			ajax: {
				url: '/filter-member',
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
		}).on('select2:select', function (e) {
			$.get('/user/' + e.params.data.id + '/addresses', function(data, status){
				if (status == 'success') {
					var addressOption = '';
					data.forEach(function(address, key) {
						addressOption += `
							<option value="` + address.id + `">` + address.name + `</option>
						`;
						if (key == 0) {
							getAddress(address.id);
						}
					});
					$('#address').html(`
						<div class="form-group">
							<label>Pilih Alamat</label>
							<select name="address_id" id="address_id" onchange="getAddress(this.value)" class="custom-select" required>
								` + addressOption + `
							</select>
							<div id="address-detail"></div>
						</div>
					`);
				}
			});
		});
		$("#stockist").select2({
			placeholder: "Cari stockist...",
			ajax: {
				url: '/stockist',
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

	$("#month").on('change', function () {
		document.getElementById("filter-month").submit();
	});
	
	$('#courier_cost').change(function(event) {
		$('#courier_text').val($('#courier_cost option:selected').text());
		// $('#price_total').val() = $('#courier_cost').val() + ($("#qty").val() * $(".product-official-big-select2").find(":selected").val());
		console.log($('#courier_text').val())
	}); 
</script>
@endsection