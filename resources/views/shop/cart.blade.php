@extends('shop.layout.app')
@section('title', 'Keranjang Belanja')
@section('style')
    <style>
        a {
            outline: none !important;
        }
    </style>
@endsection
@section('content')
    <div class="container clearfix">

        <div class="fancy-title title-border mb-4 title-center">
            <h4>Keranjang Belanja</h4>
        </div>

        <div class="row">
            <div class="col-md-9 mb-4">
                <div class="ibox">
                    @php
                        $carts_total = 0;
                    @endphp
                    @foreach ($carts as $a)
                        @php
                            $carts_total += $a->product->price_used * $a->qty;
                        @endphp
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <table class="table shoping-cart-table">
                                    <tbody>
                                        <tr>
                                            <td class="align-middle" width="90">
                                                <img class="img-cart" src="{{ asset($a->product->image_path) }}" />
                                            </td>
                                            <td class="desc align-middle">
                                                <h5>
                                                    <a href="{{ url('product/' . $a->product->dash_name) }}"
                                                        class="text-navy">
                                                        {{ $a->product->name }}
                                                    </a>
                                                </h5>
                                                <p class="small mb-1">
                                                    {!! $a->product->content ? str_limit($a->product->content) : 'Tidak ada keterangan produk' !!}
                                                </p>
                                                <dl class="small mb-1">
                                                    <dt>Kategori</dt>
                                                    <dd class="mb-0">
                                                        {{ $a->product->category ? $a->product->category->name : 'Tidak ada kategori' }}.
                                                    </dd>
                                                    {{-- <dt>Stock</dt>
                                            <dd>{{ $a->product->qty }} pcs.</dd> --}}
                                                </dl>
                                                <div class="m-t-sm">
                                                    <a href="#delete{{ $a->id }}"
                                                        class="button nott fw-normal ms-1 my-0" data-bs-toggle="modal"
                                                        style="padding: 5px 10px; font-size: 12px;">Hapus produk</a>
                                                </div>
                                            </td>

                                            <td class="align-middle text-end">
                                                Rp&nbsp;{{ number_format($a->product->price_used, 0, ',', '.') }}
                                            </td>
                                            <td class="align-middle" width="100">
                                                <form class="mb-0" action="{{ url('cart/' . $a->id) }}" method="POST">
                                                    @method('PUT')
                                                    @csrf
                                                    <input type="text" name="qty"
                                                        class="form-control sm-form-control" value="{{ $a->qty }}"
                                                        required onchange="this.form.submit()"
                                                        style="min-width: 50px; text-align: center;" />
                                                </form>
                                            </td>
                                            <td class="align-middle text-end">
                                                <h4 class="mb-0">
                                                    Rp&nbsp;{{ number_format($a->product->price_used * $a->qty, 0, ',', '.') }}
                                                </h4>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                    <div class="ibox-content">
                        @if ($carts->count())
                            <a href="{{ url(request()->carts ? 'buy?carts=' . request()->carts : 'buy') }}"
                                class="button nott fw-normal ms-1 my-0 btn-primary">Checkout</a>
                        @endif
                        <a href="{{ url('product') }}" class="button nott fw-normal ms-1 my-0">Lanjut belanja</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Ringkasan Belanja</h5>
                    </div>
                    <div class="ibox-content">
                        <span>
                            Total
                        </span>
                        <h2 class="font-bold">
                            Rp&nbsp;{{ number_format($carts_total, 0, ',', '.') }}
                        </h2>

                        <hr />
                        <span class="text-muted small">
                            *Tidak termasuk biaya pengiriman
                        </span>
                        @if ($carts->count())
                            <div class="m-t-sm mt-1">
                                <div class="btn-group">
                                    <a href="{{ url(request()->carts ? 'buy?carts=' . request()->carts : 'buy') }}"
                                        class="button nott fw-normal ms-1 my-0"
                                        style="padding: 5px 10px; font-size: 12px">Checkout</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Support</h5>
                    </div>
                    <div class="ibox-content text-center">
                        <h3>+62 {{ App\Models\ContactUs::first()->phone }}</h3>
                        <span class="small">
                            Silahkan kontak dengan kami jika Anda memiliki pertanyaan. Kami tersedia 24 jam.
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @foreach ($carts as $a)
        <form action="{{ url('cart/' . $a->id) }}" method="POST">
            @method('DELETE')
            @csrf
            <div class="modal fade" id="delete{{ $a->id }}" tabindex="-1" aria-labelledby="category"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Hapus</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Apakah anda yakin menghapus produk ini dari keranjang?
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
    @endforeach
@endsection
@section('script')
    @if (Auth::guest())
        <script>
            $(document).ready(function() {
                if (localStorage.getItem('carts') && !window.location.href.includes('?')) {
                    // check localStorage items exist
                    var carts = localStorage.getItem('carts');
                    var input = {
                        '_token': '{{ csrf_token() }}',
                        'carts': carts
                    };
                    $.post("/cart/check", input, function(data) {
                        if (data) {
                            if (data == '[]') {
                                localStorage.removeItem('carts', data);
                            } else {
                                localStorage.setItem('carts', data);
                                window.location.href = '/cart?carts=' + localStorage.getItem('carts');
                            }
                        }
                    });
                }
            });
        </script>
    @endif
@endsection
