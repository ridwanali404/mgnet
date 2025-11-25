@extends('shop.layout.app')
@section('title', 'Beranda')
@section('content')
@if ($categories->count())
<div class="container clearfix">

    <!-- Shop Categories
    ============================================= -->
    <div class="fancy-title title-border title-center mb-4">
        <h4>Kategori</h4>
    </div>

    <div class="row shop-categories clearfix justify-content-center">
        @foreach( $categories as $a )
        <div class="col-lg-4">
            <a href="{{ url('product').'?category='.$a->dash_name }}" style="background: url({{ asset($a->image_path) }}) no-repeat right center; background-size: cover;">
                <div class="vertical-middle dark center">
                    <div class="heading-block m-0 border-0">
                        <h3 class="nott fw-semibold ls0">{{ $a->name }}</h3>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

</div>

<div class="clear"></div>
@endif

<!-- New Arrivals Men
============================================= -->
<div class="container clearfix">

    <div class="fancy-title title-border mb-4 title-center {{ $categories->count() ? 'topmargin-sm' : '' }}">
        <h4>Produk terbaru</h4>
    </div>

    <div class="row grid-6">

        @foreach( $products as $a )
        <div class="col-lg-2 col-md-3 col-6 px-2">
            <div class="product">
                <div class="product-image">
                    <a href="{{ url('product/'.$a->dash_name) }}"><img src="{{ asset($a->image_path) }}" alt="{{ $a->name }}"></a>
                    @if ($a->images && count($a->images) > 1)
                    <a href="{{ url('product/'.$a->dash_name) }}"><img src="{{ asset('/storage/'.$a->images[1]) }}" alt="{{ $a->name }}"></a>
                    @endif
                    <div class="bg-overlay">
                        <div class="bg-overlay-content align-items-end justify-content-between" data-hover-animate="fadeIn" data-hover-speed="400">
                            <a href="{{ url('product/'.$a->dash_name) }}" class="btn btn-dark me-2 rounded-0"><i class="icon-shopping-basket"></i></a>
                        </div>
                        <div class="bg-overlay-bg bg-transparent"></div>
                    </div>
                </div>
                <div class="product-desc">
                    <div class="product-title mb-1"><h3><a href="{{ url('product/'.$a->dash_name) }}">{{ $a->name }}</a></h3></div>
                    <div class="product-price font-primary mb-0"><ins>Rp {{ number_format($a->price_used, 0, ',', '.') }}</ins></div>
                    @if(Auth::user() && Auth::user()->premiumUserPin && $a->is_ro)
                    <div class="product-rating"><small>{{ $a->poin ?? 0 }} PV</small></div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

    </div>

</div>
@endsection
