@extends('marketplace.layouts.inspinia')
@section('title')
    Product
@endsection
@section('style')
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
                <h1>Your Carts</h1>
                <!-- <p>Donec sed odio dui. Etiam porta sem malesuada magna mollis euismod.</p> -->
                <p>These are your product carts.</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-9">
                <div class="ibox">
                    <div class="ibox-title">
                        <span class="pull-right">(<strong>{{ $carts->count() }}</strong>) items</span>
                        <h5>Items in your cart</h5>
                    </div>
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
                                            <td width="90">
                                                <img class="img-cart" src="{{ asset($a->product->image_path) }}" />
                                            </td>
                                            <td class="desc">
                                                <h3>
                                                    <a href="{{ url('product/' . $a->product->dash_name) }}"
                                                        class="text-navy">
                                                        {{ $a->product->name }}
                                                    </a>
                                                </h3>
                                                <p class="small">
                                                    {!! str_limit($a->product->content) !!}
                                                </p>
                                                <dl class="small m-b-none">
                                                    <dt>Category</dt>
                                                    <dd>{{ $a->product->category ? $a->product->category->name : 'No category' }}.
                                                    </dd>
                                                    {{-- <dt>Stock</dt>
                                            <dd>{{ $a->product->qty }} pcs.</dd> --}}
                                                </dl>
                                                <div class="m-t-sm">
                                                    <a href="#" class="text-muted" data-toggle="modal"
                                                        data-target="#delete{{ $a->id }}"><i class="fa fa-trash"></i>
                                                        Remove
                                                        item</a>
                                                    <div class="modal inmodal" id="delete{{ $a->id }}" tabindex="-1"
                                                        role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content animated fadeInDown">
                                                                <form action="{{ url('cart/' . $a->id) }}" method="POST">
                                                                    @method('DELETE')
                                                                    @csrf
                                                                    <div class="modal-header">
                                                                        <button type="button" class="close"
                                                                            data-dismiss="modal"><span
                                                                                aria-hidden="true">&times;</span><span
                                                                                class="sr-only">Close</span></button>
                                                                        <i class="fa fa-trash modal-icon"></i>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        Are you sure want to delete this item?
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="submit"
                                                                            class="btn btn-danger">Delete</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                Rp&nbsp;{{ number_format($a->product->price_used) }}
                                            </td>
                                            <td width="100">
                                                <form action="{{ url('cart/' . $a->id) }}" method="POST">
                                                    @method('PUT')
                                                    @csrf
                                                    <input type="text" name="qty" class="form-control"
                                                        value="{{ $a->qty }}" required onchange="this.form.submit()"
                                                        style="width: 50px; text-align: center;" />
                                                </form>
                                            </td>
                                            <td>
                                                <h4>
                                                    Rp&nbsp;{{ number_format($a->product->price_used * $a->qty) }}
                                                </h4>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                    <div class="ibox-content">
                        <a href="{{ url(request()->carts ? 'buy?carts=' . request()->carts : 'buy') }}"
                            class="btn btn-primary pull-right" style="padding: 6px 12px"><i
                                class="fa fa fa-shopping-cart"></i> Checkout</a>
                        <a href="{{ url('product') }}" class="btn btn-white"><i class="fa fa-arrow-left"></i> Continue
                            shopping</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Cart Summary</h5>
                    </div>
                    <div class="ibox-content">
                        <span>
                            Total
                        </span>
                        <h2 class="font-bold">
                            Rp&nbsp;{{ number_format($carts_total) }}
                        </h2>

                        <hr />
                        <span class="text-muted small">
                            *Exclude shipping cost
                        </span>
                        <div class="m-t-sm">
                            <div class="btn-group">
                                <a href="{{ url(request()->carts ? 'buy?carts=' . request()->carts : 'buy') }}"
                                    class="btn btn-primary btn-sm" style="padding: 5px 10px; font-size: 12px"><i
                                        class="fa fa-shopping-cart"></i>
                                    Checkout</a>
                                <a href="#" class="btn btn-white btn-sm"> Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Support</h5>
                    </div>
                    <div class="ibox-content text-center">
                        <h3><i class="fa fa-phone"></i> +62 {{ App\Models\ContactUs::first()->phone }}</h3>
                        <span class="small">
                            Please contact with us if you have any questions. We are avalible 24h.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
