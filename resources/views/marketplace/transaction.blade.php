@extends('marketplace.layouts.inspinia')
@section('title')
    Product
@endsection
@section('style')
    <link href="{{ asset('bootstrap-imageupload/dist/css/bootstrap-imageupload.min.css') }}" rel="stylesheet">
    <style>
        a {
            outline: none !important;
        }
    </style>
@endsection
@section('content')
    <section id="products" class="container services animated fadeInDown">
        <div class="row m-b-lg">
            <div class="col-lg-12 text-center">
                <div class="navy-line"></div>
                <h1>Your Transaction</h1>
                <p>These are your transactions.</p>
                @if (Auth::user())
                    <p>
                        <strong>
                            @if (!request()->type)
                                <a href="{{ url('transaction?type=referral') }}">Transaksi oleh Referral</a>
                            @else
                                <a href="{{ url('transaction') }}">Transaksi Pribadi</a>
                            @endif
                        </strong>
                    </p>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 wow fadeInUp">
                <div class="form-group m-b">
                    <form action="{{ url('transaction') }}" method="GET">
                        <input type="text" name="code" value="{{ request()->code }}" placeholder="Transaction code..."
                            class="form-control" style="border: none" />
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @foreach ($transactions as $transaction)
                    <div class="ibox m-b-xl">
                        <div class="ibox-title">
                            @if ($transaction->status == 'pending')
                                <span class="pull-right label label-warning">Menunggu pembayaran</span>
                            @elseif($transaction->status == 'expired')
                                <span class="pull-right label label-danger">Kadaluarsa</span>
                            @elseif($transaction->status == 'paid')
                                <span class="pull-right label label-success">Sudah dibayar</span>
                            @elseif($transaction->status == 'shipped')
                                <span class="pull-right label label-info">Sedang dikirim</span>
                            @elseif($transaction->status == 'received')
                                <span class="pull-right label label-primary">Diterima</span>
                            @endif
                            <h5 class="hidden-xs">#{{ $transaction->created_at->format('YmdHis') }}</h5>
                        </div>
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <table class="table shoping-cart-table">
                                    <tbody>
                                        @foreach ($transaction->carts as $cart)
                                            <tr>
                                                <td width="90">
                                                    <img class="img-cart" src="{{ asset($cart->product->image_path) }}" />
                                                </td>
                                                <td class="desc">
                                                    <h3>
                                                        <a href="{{ url('product/' . $cart->product->dash_name) }}"
                                                            class="text-navy">
                                                            {{ $cart->name }}
                                                        </a>
                                                    </h3>
                                                    <p class="small">{{ $cart->product->category->name ?? 'No category' }}
                                                    </p>
                                                </td>

                                                <td>
                                                    <h4 style="font-weight: normal;">
                                                        Rp&nbsp;{{ number_format($cart->price) }}
                                                    </h4>
                                                    <!-- <s class="small text-muted">$230,00</s> -->
                                                </td>
                                                <td width="65">
                                                    <input id="qty" type="text" name="qty" class="form-control"
                                                        value="{{ $cart->qty }}" readonly>
                                                </td>
                                                <td>
                                                    <h4>
                                                        Rp&nbsp;{{ number_format($cart->total) }}
                                                    </h4>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td width="90">
                                                <h4>
                                                    Courier
                                                </h4>
                                            </td>
                                            <td class="desc">
                                                <h4 style="font-weight: normal; line-height: 1.5;">
                                                    {{ $transaction->shipment }}<br />
                                                    <small>Shipment Number
                                                        <b>{{ $transaction->shipment_number ?? '-' }}</b></small>
                                                </h4>
                                            </td>
                                            <td>
                                            </td>
                                            <td width="65">
                                            </td>
                                            <td>
                                                <h4>
                                                    Rp&nbsp;{{ number_format($transaction->shipment_fee) }}
                                                </h4>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="90">

                                            </td>
                                            <td class="desc">
                                                @if ($transaction->status == 'pending')
                                                    <p>Silahkan transfer sejumlah
                                                        <b>Rp&nbsp;{{ number_format($transaction->price_total) }}</b> ke
                                                        rekening
                                                        {{ \App\Models\User::where('type', 'admin')->first()->bank->name }}
                                                        <b>{{ \App\Models\User::where('type', 'admin')->first()->bank_account }}</b>
                                                        atau
                                                        {{ \App\Models\Bank::where('name', 'BANK BCA')->first()->name }}
                                                        <b>0376 155 166</b> a/n
                                                        {{ \App\Models\User::where('type', 'admin')->first()->bank_as }}
                                                    </p>
                                                @endif
                                                @if ($transaction->receipt && $transaction->status == 'pending')
                                                    <a href="#" type="button" data-toggle="modal"
                                                        data-target=".image{{ $transaction->id }}">View receipt</a>
                                                @endif
                                            </td>
                                            <td>
                                            </td>
                                            <td width="65">
                                            </td>
                                            <td>
                                                @if ($transaction->receipt || $transaction->is_paid)
                                                    <h4>
                                                        Rp&nbsp;{{ number_format($transaction->price_total) }}
                                                    </h4>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if ($transaction->user_id == Auth::id())
                            @if ($transaction->status == 'pending')
                                <div class="ibox-content">
                                    <button class="btn btn-primary pull-right" style="padding: 6px 12px" data-toggle="modal"
                                        data-target="#update{{ $transaction->id }}"><i class="fa fa-upload"></i> Upload
                                        receipt</button>
                                    <button class="btn btn-white" data-toggle="modal"
                                        data-target="#delete{{ $transaction->id }}"><i class="fa fa-trash"></i><span
                                            class="hidden-xs"> Remove transaction</span></button>
                                </div>
                            @elseif($transaction->status == 'shipped')
                                <div class="ibox-content">
                                    <button class="btn btn-primary pull-right" style="padding: 6px 12px" data-toggle="modal"
                                        data-target="#confirm{{ $transaction->id }}"><i class="fa fa-check"></i> Pesanan
                                        diterima</button>
                                </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @foreach ($transactions as $transaction)
        @if ($transaction->user_id == Auth::id())
            @if ($transaction->receipt && $transaction->status == 'pending')
                )
                <div class="modal inmodal image{{ $transaction->id }}" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content animated fadeInDown">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal"><span
                                        aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
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
            @if ($transaction->status == 'pending')
                <div class="modal inmodal" id="update{{ $transaction->id }}" tabindex="-1" role="dialog"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content animated fadeInDown">
                            <form action="{{ url('transaction/' . $transaction->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @method('PUT')
                                @csrf
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><span
                                            aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                    <i class="fa fa-picture-o modal-icon"></i>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group row">
                                        <label class="col-sm-2 control-label">Image</label>
                                        <div class="col-sm-10">
                                            <div class="imageupload">
                                                <div class="file-tab">
                                                    <label class="btn btn-default btn-file">
                                                        <span>Browse</span>
                                                        <!-- The file is stored here. -->
                                                        <input type="file" name="receipt" required>
                                                    </label>
                                                    <button type="button" class="btn btn-default">Remove</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-white">Upload receipt</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal inmodal" id="delete{{ $transaction->id }}" tabindex="-1" role="dialog"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content animated fadeInDown">
                            <form action="{{ url('transaction/' . $transaction->id) }}" method="POST">
                                @method('DELETE')
                                @csrf
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><span
                                            aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                    <i class="fa fa-trash modal-icon"></i>
                                </div>
                                <div class="modal-body">
                                    Are you sure want to delete this item?
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @elseif($transaction->status == 'shipped')
                <div class="modal inmodal" id="confirm{{ $transaction->id }}" tabindex="-1" role="dialog"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content animated fadeInDown">
                            <form action="{{ url('transaction/' . $transaction->id . '/received') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><span
                                            aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                    <i class="fa fa-check modal-icon"></i>
                                </div>
                                <div class="modal-body">
                                    Apakah anda yakin?
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Konfirmasi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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
