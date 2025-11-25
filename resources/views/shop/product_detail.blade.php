@extends('shop.layout.app')
@section('title', $product->name)
@section('style')
@endsection
@section('content')
<div class="container clearfix">

    <div class="fancy-title title-border mb-4 title-center">
        <h4>{{ $product->name }}</h4>
    </div>

    <div class="ibox product-detail">
        <div class="ibox-content">
            <div class="row">
                <div class="col-md-5">
                    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            @if($product->images)
                            @foreach( $product->images as $a )
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $loop->index }}" {{ $loop->first ? 'class=active aria-current=true' : '' }} aria-label="Slide {{ $loop->index + 1 }}"></button>
                            @endforeach
                            @else
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                            @endif
                        </div>
                        <div class="carousel-inner">
                            @if($product->images)
                            @foreach( $product->images as $a )
                            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                <img src="{{ asset('storage/'.$a) }}" class="d-block w-100" alt="{{ $product->name }}">
                            </div>
                            @endforeach
                            @else
                            <div class="carousel-item active">
                                <img src="{{ asset('img/default-product-image.jpg') }}" class="d-block w-100" alt="{{ $product->name }}">
                            </div>
                            @endif
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
                <div class="col-md-7">
                    <div>
                        @if(Auth::user())
                        <form action="{{ url('cart') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}" />
                            <button type="submit" class="button nott fw-normal ms-1 my-0 float-end">Tambah ke Keranjang</button>
                        </form>
                        @else
                        <input type="hidden" name="product_id" value="{{ $product->id }}" />
                        <button type="button" class="button nott fw-normal ms-1 my-0 float-end add-to-cart">Tambah ke Keranjang</button>
                        @endif
                        <h3 class="product-main-price">Rp&nbsp;{{ number_format($product->price_used) }}
                            @if(Auth::user() && Auth::user()->member && Auth::user()->member->member_phase_name != 'User Free' && $product->is_ro)
                            <small><br class="visible-xs" />{{ $product->poin ?? 0 }} PV</small>
                            @endif
                            <small class="text-muted"><br class="visible-xs" />Tidak termasuk biaya pengiriman</small>
                        </h3>
                    </div>
                    <hr>
                    <h4>Deskripsi produk</h4>
                    <div class="small text-muted">
                        {!! $product->desc ?? 'Tidak ada deskripsi produk' !!}
                    </div>
                    <hr>
                    @if($product->is_big)
                    <h4>Isi Produk</h4>
                    <div class="small text-muted">
                        @foreach( $product->bigProducts as $a )
                        <div class="ibox-content">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="align-middle" width="90">
                                                <img class="img-cart" src="{{ asset($a->childProduct->image_path) }}" />
                                            </td>
                                            <td class="desc align-middle">
                                                <h5>
                                                    <a href="{{ url('product/'.$a->childProduct->dash_name) }}" class="text-navy">
                                                        {{ $a->childProduct->name }}
                                                    </a>
                                                </h5>
                                                <p class="small mb-1">
                                                    {!! $a->childProduct->content ? str_limit($a->childProduct->content) : 'Tidak ada keterangan produk' !!}
                                                </p>
                                            </td>
                                            <td class="align-middle text-end">
                                                <h4 class="mb-0">
                                                    {{ number_format($a->qty, 0, ',', '.') }} pcs
                                                </h4>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @if(false)
                    <dl class="dl-horizontal m-t-md small">
                        <dt>Stock</dt>
                        <dd>{{ $product->qty }} pcs.</dd>
                        <dt>Sold Out</dt>
                        <dd>{{ $product->sold }} pcs.</dd>
                        <dt>Last Updated</dt>
                        <dd>{{ $product->updated_at }}.</dd>
                    </dl>
                    @endif
                    
                    @if($product->youtube)
                    <hr>
                    <h4>Video produk</h4>
                    <iframe width="560" height="315" src="{{ $product->youtube }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    @endif
                </div>
            </div>
        </div>
        @if(false)
        <div class="ibox-footer p-b">
            <div class="row">
                <div class="col-xs-12">
                    <span class="pull-right">
                        Created at - <i class="fa fa-clock-o"></i> {{ $product->created_at }}
                    </span>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
@section('script')
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