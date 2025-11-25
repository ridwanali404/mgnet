@extends('shop.layout.app')
@section('title', 'Transaksi')
@section('style')
    <link href="{{ asset('bootstrap-imageupload/dist/css/bootstrap-imageupload.min.css') }}" rel="stylesheet">
    <style>
        a {
            outline: none !important;
        }
    </style>
@endsection
@section('content')
    <div class="container clearfix">

        <div class="fancy-title title-border mb-4 title-center">
            <h4>Transaksi</h4>
        </div>

        <div class="row">
            <div class="col wow fadeInUp">
                <div class="form-group m-b">
                    <form action="{{ url('transaction') }}" method="GET">
                        <input type="text" name="code" value="{{ request()->code }}" placeholder="Kode transaksi..."
                            class="form-control sm-form-control border-0" />
                    </form>
                </div>
            </div>
            @if (Auth::user())
                <div class="col-auto text-end">
                    @if (!request()->type)
                        <a class="button nott fw-normal ms-1 my-0" href="{{ url('transaction?type=referral') }}">Transaksi
                            oleh Referral</a>
                    @else
                        <a class="button nott fw-normal ms-1 my-0" href="{{ url('transaction') }}">Transaksi Pribadi</a>
                    @endif
                </div>
            @endif
        </div>
        <div class="row">
            <div class="col-md-12">
                @foreach ($transactions as $transaction)
                    <div class="ibox m-b-xl">
                        <hr>
                        <div class="ibox-title row">
                            <h5 class="hidden-xs col mb-0">#{{ $transaction->created_at->format('YmdHis') }}
                                {{ in_array($transaction->type, ['stockist', 'masterstockist']) ? '(belanja stok)' : '' }}
                            </h5>
                            @if ($transaction->status == 'pending')
                                <span class="pull-right label label-warning col-auto">Menunggu pembayaran</span>
                            @elseif($transaction->status == 'expired')
                                <span class="pull-right label label-danger col-auto">Kadaluarsa</span>
                            @elseif($transaction->status == 'paid')
                                <span class="pull-right label label-success col-auto">Sedang dikemas</span>
                            @elseif($transaction->status == 'packed')
                                <span class="pull-right label label-info col-auto">Sudah masuk ekspedisi</span>
                            @elseif($transaction->status == 'shipped')
                                <span class="pull-right label label-info col-auto">Sedang dikirim</span>
                            @elseif($transaction->status == 'received')
                                <span class="pull-right label label-primary col-auto">Diterima</span>
                            @endif
                        </div>
                        @if (request()->type == 'referral')
                            <hr>
                            <div class="ibox-title row">
                                <h5 class="hidden-xs col mb-0">Pembeli</h5>
                                <span class="pull-right col-auto">
                                    @if ($transaction->user)
                                        <a href="{{ url('user/' . $transaction->user->id . '/profile') }}">
                                            {{ $transaction->user->name }}<br>
                                            <small>{{ $transaction->user->username }}</small>
                                        </a>
                                    @else
                                        {{ $transaction->address->recipient }}
                                    @endif
                                </span>
                            </div>
                        @endif
                        <hr>
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <table class="table shoping-cart-table">
                                    <tbody>
                                        @foreach ($transaction->carts as $cart)
                                            <tr>
                                                <td class="align-middle" width="90">
                                                    <img class="img-cart" src="{{ asset($cart->product->image_path) }}" />
                                                </td>
                                                <td class="desc align-middle">
                                                    <h5 class="mb-1">
                                                        <a href="{{ url('product/' . $cart->product->dash_name) }}"
                                                            class="text-navy">
                                                            {{ $cart->name }}
                                                        </a>
                                                    </h5>
                                                    <p class="small mb-1">
                                                        {{ $cart->product->category->name ?? 'Tidak ada kategori' }}</p>
                                                </td>
                                                <td class="align-middle text-end">
                                                    <h4 class="mb-0">
                                                        Rp&nbsp;{{ number_format($cart->price, 0, ',', '.') }}
                                                    </h4>
                                                    <!-- <s class="small text-muted">$230,00</s> -->
                                                </td>
                                                <td class="align-middle" width="65">
                                                    <input type="text" name="qty"
                                                        class="form-control sm-form-control" value="{{ $cart->qty }}"
                                                        readonly style="min-width: 50px; text-align: center;">
                                                </td>
                                                <td class="align-middle text-end">
                                                    <h4 class="mb-0">
                                                        Rp&nbsp;{{ number_format($cart->price_total, 0, ',', '.') }}
                                                    </h4>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td class="align-middle" width="90">
                                                <h4 class="mb-0">
                                                    Kurir
                                                </h4>
                                            </td>
                                            <td class="desc align-middle">
                                                <h4 class="mb-0">
                                                    {{ $transaction->shipment ?? '-' }}<br />
                                                    <small class="fw-normal">Nomor Resi Pengiriman
                                                        <b>{{ $transaction->shipment_number ?? '-' }}</b></small><br>
                                                    <small class="fw-normal">Tujuan <b>({{ $transaction->address->name }})
                                                            {{ $transaction->address->address }},
                                                            {{ $transaction->address->subdistrict->subdistrict_name }},
                                                            {{ $transaction->address->city->city_name }},
                                                            {{ $transaction->address->province->province }}
                                                            {{ $transaction->address->postal_code }}</b></small>
                                                </h4>
                                            </td>
                                            <td>
                                            </td>
                                            <td width="65">
                                            </td>
                                            <td class="align-middle text-end">
                                                <h4 class="mb-0">
                                                    Rp&nbsp;{{ number_format($transaction->shipment_fee, 0, ',', '.') }}
                                                </h4>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="align-middle" width="90">
                                            </td>
                                            <td class="desc align-middle">
                                                <h4 class="mb-0">
                                                    <small class="fw-normal">Kode Unik</small><br>
                                                </h4>
                                            </td>
                                            <td>
                                            </td>
                                            <td width="65">
                                            </td>
                                            <td class="align-middle text-end">
                                                <h4 class="mb-0">
                                                    Rp&nbsp;{{ number_format($transaction->code, 0, ',', '.') }}
                                                </h4>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="align-middle" width="90">
                                            </td>
                                            <td class="desc align-middle">
                                                <h4 class="mb-0">
                                                    <small class="fw-normal">Total Bayar</small><br>
                                                </h4>
                                            </td>
                                            <td>
                                            </td>
                                            <td width="65">
                                            </td>
                                            <td class="align-middle text-end">
                                                <h4 class="mb-0">
                                                    Rp&nbsp;{{ number_format($transaction->price_total, 0, ',', '.') }}
                                                </h4>
                                            </td>
                                        </tr>
                                        @if (in_array($transaction->status, ['pending']))
                                            <tr>
                                                <td width="90">
                                                </td>
                                                <td class="desc">
                                                    @if ($transaction->status == 'pending')
                                                        <p class="mb-1">Silahkan transfer sejumlah
                                                            <b>Rp&nbsp;{{ number_format($transaction->price_total, 0, ',', '.') }}</b>
                                                            ke
                                                            rekening
                                                            {{ \App\Models\User::where('type', 'admin')->first()->bank->name }}
                                                            <b>{{ \App\Models\User::where('type', 'admin')->first()->bank_account }}</b>
                                                            a/n
                                                            {{ \App\Models\User::where('type', 'admin')->first()->bank_as }}
                                                            @if (false)
                                                                atau
                                                                {{ \App\Models\Bank::where('name', 'BANK BCA')->first()->name }}
                                                                <b>0376 155 166</b> a/n
                                                                {{ \App\Models\User::where('type', 'admin')->first()->bank_as }}
                                                            @endif
                                                        </p>
                                                    @endif
                                                    @if ($transaction->receipt && $transaction->status == 'pending')
                                                        <a href="#" type="button" data-bs-toggle="modal"
                                                            data-bs-target=".image{{ $transaction->id }}"><strong>Lihat
                                                                resi pembayaran</strong></a>
                                                    @endif
                                                </td>
                                                <td>
                                                </td>
                                                <td width="65">
                                                </td>
                                                <td class="align-middle text-end">
                                                    @if ($transaction->receipt || $transaction->is_paid)
                                                        <h4 class="mb-0">
                                                            Rp&nbsp;{{ number_format($transaction->price_total, 0, ',', '.') }}
                                                        </h4>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if ($transaction->user_id == Auth::id())
                            @if ($transaction->status == 'pending')
                                <div class="ibox-content mb-5">
                                    <button class="button nott fw-normal ms-1 my-1" data-bs-toggle="modal"
                                        data-bs-target="#update{{ $transaction->id }}">{{ $transaction->receipt ? 'Ubah' : 'Unggah' }}
                                        Resi Pembayaran</button>
                                    @if (false)
                                        <button class="button nott fw-normal ms-1 my-1" data-bs-toggle="modal"
                                            data-bs-target="#delete{{ $transaction->id }}">Hapus Transaksi</button>
                                    @endif
                                </div>
                            @elseif($transaction->status == 'shipped')
                                <div class="ibox-content mb-5">
                                    <button class="button nott fw-normal ms-1 my-1" data-bs-toggle="modal"
                                        data-bs-target="#confirm{{ $transaction->id }}">Konfirmasi Barang
                                        Diterima</button>
                                </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    @foreach ($transactions as $transaction)
        @if ($transaction->user_id == Auth::id())
            @if ($transaction->receipt && $transaction->status == 'pending')
                )
                <div class="modal fade image{{ $transaction->id }}" tabindex="-1" aria-labelledby="category"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Resi Pembayaran</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="{{ asset($transaction->receipt) }}" />
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="button nott fw-normal ms-1 my-0"
                                    data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if ($transaction->status == 'pending')
                <form action="{{ url('transaction/' . $transaction->id) }}" method="POST" enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <div class="modal fade" id="update{{ $transaction->id }}" tabindex="-1" aria-labelledby="category"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ $transaction->receipt ? 'Ubah' : 'Unggah' }} Resi
                                        Pembayaran</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Resi Pembayaran</label>
                                        <input class="form-control" type="file" name="receipt">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="button nott fw-normal ms-1 my-0"
                                        data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit"
                                        class="button nott fw-normal ms-1 my-0">{{ $transaction->receipt ? 'Ubah' : 'Unggah' }}
                                        Resi</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                @if (false)
                    <form action="{{ url('transaction/' . $transaction->id) }}" method="POST">
                        @method('DELETE')
                        @csrf
                        <div class="modal fade" id="delete{{ $transaction->id }}" tabindex="-1"
                            aria-labelledby="category" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Hapus</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah anda yakin menghapus transaksi ini?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="button nott fw-normal ms-1 my-0"
                                            data-bs-dismiss="modal">Tutup</button>
                                        <button type="submit" class="button nott fw-normal ms-1 my-0">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif
            @elseif($transaction->status == 'shipped')
                <form action="{{ url('transaction/' . $transaction->id . '/received') }}" method="POST">
                    @method('PUT')
                    @csrf
                    <div class="modal fade" id="confirm{{ $transaction->id }}" tabindex="-1"
                        aria-labelledby="category" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Konfirmasi</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Apakah anda yakin akan mengonfirmasi barang telah sampai?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="button nott fw-normal ms-1 my-0"
                                        data-bs-dismiss="modal">Tutup</button>
                                    <button type="submit" class="button nott fw-normal ms-1 my-0">Konfirmasi</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        @endif
    @endforeach
@endsection
@section('script')
    <script src="{{ asset('bootstrap-imageupload/dist/js/bootstrap-imageupload.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.imageupload').imageupload({
                maxFileSizeKb: 1024
            });
        });
    </script>
    @if (Auth::guest())
        <script>
            $(document).ready(function() {
                if (localStorage.getItem('transactions')) {
                    var transactions = JSON.parse(localStorage.getItem('transactions'));
                    if (!window.location.href.includes('?')) {
                        window.location.href = '/transaction?transactions=' + localStorage.getItem('transactions');
                    }
                } else {
                    var transactions = [];
                }
            });
        </script>
    @endif
@endsection
