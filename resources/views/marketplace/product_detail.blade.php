@extends('marketplace.layouts.inspinia')
@section('title')
Detail
@endsection
@section('style')
<link href="{{ asset('inspinia/css/plugins/blueimp/css/blueimp-gallery.min.css') }}" rel="stylesheet">
<link href="{{ asset('inspinia/css/plugins/slick/slick.css') }}" rel="stylesheet">
<link href="{{ asset('inspinia/css/plugins/slick/slick-theme.css') }}" rel="stylesheet">
@endsection
@section('content')
<section class="container animated fadeInUp">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox product-detail">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="product-images">
                                @if($product->images)
                                @foreach( $product->images as $a )
                                <div>
                                    <a href="{{ asset('storage/'.$a) }}" title="{{ $product->name }}"
                                        data-gallery=""><img class="img-product-detail"
                                            src="{{ asset('storage/'.$a) }}" /></a>
                                </div>
                                @endforeach
                                @else
                                <div>
                                    <a href="{{ asset('img/default-product-image.jpg') }}" title="{{ $product->name }}"
                                        data-gallery=""><img class="img-product-detail"
                                            src="{{ asset('img/default-product-image.jpg') }}" /></a>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h2 class="font-bold m-b-xs">
                                {{ $product->name }}
                            </h2>
                            <hr>
                            <div>
                                @if(Auth::user())
                                <form action="{{ url('cart') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}" />
                                    <button type="submit" class="btn btn-primary pull-right hidden-xs">Add
                                        to
                                        cart</button>
                                    <button type="submit" class="btn btn-primary btn-block visible-xs">Add
                                        to
                                        cart</button>
                                </form>
                                @else
                                <input type="hidden" name="product_id" value="{{ $product->id }}" />
                                <button type="button" class="btn btn-primary pull-right hidden-xs add-to-cart">Add
                                    to
                                    cart</button>
                                <button type="button" class="btn btn-primary btn-block visible-xs add-to-cart">Add
                                    to
                                    cart</button>
                                @endif
                                <h1 class="product-main-price">Rp&nbsp;{{ number_format($product->price_used) }} <small
                                        class="text-muted"><br class="visible-xs" />Exclude Shipping Charges</small>
                                </h1>
                            </div>
                            <hr>
                            <h4>Product description</h4>
                            <div class="small text-muted">
                                {!! $product->desc !!}
                            </div>
                            <!-- <dl class="dl-horizontal m-t-md small">
                                    <dt>Stock</dt>
                                    <dd>{{ $product->qty }} pcs.</dd>
                                    <dt>Sold Out</dt>
                                    <dd>{{ $product->sold }} pcs.</dd>
                                    <dt>Last Updated</dt>
                                    <dd>{{ $product->updated_at }}.</dd>
                                </dl> -->
                        </div>
                    </div>
                </div>
                <div class="ibox-footer p-b">
                    <div class="row">
                        <div class="col-xs-12">
                            <span class="pull-right">
                                Created at - <i class="fa fa-clock-o"></i> {{ $product->created_at }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div id="blueimp-gallery" class="blueimp-gallery">
    <div class="slides"></div>
    <h3 class="title"></h3>
    <a class="prev">‹</a>
    <a class="next">›</a>
    <a class="close">×</a>
    <a class="play-pause"></a>
    <ol class="indicator"></ol>
</div>
@endsection
@section('script')
<!-- blueimp gallery -->
<script src="{{ asset('inspinia/js/plugins/blueimp/jquery.blueimp-gallery.min.js') }}"></script>
<!-- slick carousel-->
<script src="{{ asset('inspinia/js/plugins/slick/slick.min.js') }}"></script>
<script>
    $(document).ready(function(){
        $('.product-images').slick({
            dots: true
        });
    });
</script>
@if(Auth::guest())
<script>
    $(document).ready(function(){
        $('.add-to-cart').click(function () {
            var carts = localStorage.getItem('carts') ? JSON.parse(localStorage.getItem('carts')) : [];
            var input = {
                '_token': '{{ csrf_token() }}',
                'product_id': $("input[name=product_id]").val(),
                'carts': carts
            };
            $.post("/cart", input, function(data){
                if (data) {
                    carts.push(parseInt(data));
                    localStorage.setItem('carts', JSON.stringify(carts));
                }
                window.location.href = '/cart?carts=' + JSON.stringify(carts);
            });
        });
    });
</script>
@endif
@endsection