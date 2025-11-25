@extends('marketplace.layouts.inspinia')
@section('title')
Product
@endsection
@section('style')
@endsection
@section('content')
<section id="products" class="container services animated fadeInDown">
    <div class="row m-b-lg">
        <div class="col-lg-12 text-center">
            <div class="navy-line"></div>
            <h1>Our Products</h1>
            <!-- <p>Donec sed odio dui. Etiam porta sem malesuada magna mollis euismod.</p> -->
            <p>These are our products for you.</p>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 wow fadeInUp">
            <div class="form-group m-b">
                <div class="input-group m-b">
                    <div class="input-group-btn">
                        <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#category"
                            style="padding: 6px 12px">{{ isset($category) ? $category : 'Category' }} <span
                                class="caret"></span></button>
                    </div>
                    <form action="{{ url('product') }}" method="GET">
                        @if(isset($category))
                        <input type="hidden" name="category" value="{{ $category }}" />
                        @endif
                        <input type="text" name="query" value="{{ $query }}" placeholder="Search product..."
                            class="form-control" />
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        @foreach( $products as $a )
        <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6 wow fadeInUp">
            <div class="ibox">
                <div class="ibox-content product-box">
                    <img class="img-product" src="{{ asset($a->image_path) }}" />
                    <!-- <div class="product-imitation">
                            [ INFO ]
                        </div> -->
                    <div class="product-desc">
                        <span class="product-price">
                            Rp {{ number_format($a->price) }}
                        </span>
                        <small
                            class="text-muted text-truncate">{{ $a->category ? $a->category->name : 'No category' }}</small>
                        <a href="{{ url('product/'.$a->dash_name) }}" class="product-name text-truncate">
                            {{ $a->name }}</a>
                        <!-- <div class="small m-t-xs">
                                Many desktop publishing packages and web page editors now.
                            </div> -->
                        <div class="m-t text-righ">
                            <a href="{{ url('product/'.$a->dash_name) }}"
                                class="btn btn-xs btn-outline btn-primary">Info <i class="fa fa-long-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <!-- <p class="pull-right wow fadeInUp" style="margin-bottom: 20px">
            <a href="{{ url('product') }}" class="btn btn-outline btn-primary">See more products...</a>
        </p> -->
</section>

<div class="modal inmodal" id="category" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated fadeInDown">
            @csrf
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span
                        class="sr-only">Close</span></button>
                <h4 class="modal-title">Category</h4>
                <small class="font-bold">These are our category for you.</small>
            </div>
            <div class="modal-body">
                <div class="row">
                    @foreach( $categories as $a )
                    <div class="col-md-3 col-sm-6 col-xs-12 fadeInUp">
                        <div class="ibox">
                            <div
                                class="ibox-content product-box {{ isset($category) ? $category == $a->name ? 'active' : '' :''}}">
                                <img class="img-category" src="{{ asset($a->image_path) }}" />
                                <div class="product-desc">
                                    <a href="#" class="product-name text-truncate"> {{ $a->name }}</a>
                                    <div class="m-t text-righ">
                                        <form action="{{ url('product') }}" method="GET">
                                            @if(isset($query))
                                            <input type="hidden" name="query" value="{{ $query }}" />
                                            @endif
                                            <input type="hidden" name="category" value="{{ $a->dash_name }}" />
                                            <button type="submit" class="btn btn-xs btn-outline btn-primary">See
                                                products <i class="fa fa-long-arrow-right"></i> </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                <a href="{{ url('product') }}" class="btn btn-primary {{ !isset($category) ? 'disabled' : '' }}"
                    style="padding: 6px 12px">All Category</a>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(document).ready(function () {
        $(".img-category").css({'height' : $(".img-category")[0].width});
        $(".img-product").css({'height' : $(".img-product")[0].width});
    });
    $(window).on('resize', function(){
        $(".img-category").css({'height' : $(".img-category")[0].width});
        $(".img-product").css({'height' : $(".img-product")[0].width});
    });
    $('#category').on('shown.bs.modal', function (e) {
        $(".img-category").css({'height' : $(".img-category")[0].width});
        $(".img-product").css({'height' : $(".img-product")[0].width});
    })
</script>
@endsection