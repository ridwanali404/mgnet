@extends('marketplace.layouts.inspinia')
@section('title')
Blog
@endsection
@section('style')
@endsection
@section('content')
<section id="blogs" class="container">
    <div class="row m-b-lg">
        <div class="col-lg-12 text-center">
            <div class="navy-line"></div>
            <h1>Blogs</h1>
            <p>These are our blogs for you.</p>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 wow fadeInUp">
            <div class="form-group m-b">
                <form action="{{ url('blog') }}" method="GET">
                    <input type="text" name="query" value="{{ $query }}" placeholder="Search blog..."
                        class="form-control" style="border: none" />
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        @foreach( $blogs as $a )
        <div class="col-md-6 wow fadeInUp">
            <div class="ibox">
                <div class="ibox-content product-box">
                    <img class="img-blog" src="{{ asset($a->image_path) }}" />
                    <div class="product-desc">
                        <a href="{{ url('blog/'.$a->dash_title) }}"
                            class="product-name text-truncate">{{ $a->title }}</a>
                        <div class="small m-t-xs">
                            <span class="fa fa-clock-o fa-fw"></span>{{ $a->created_at }}<br />
                            <!-- Many desktop publishing packages and web page editors now. -->
                        </div>
                        <div class="m-t text-righ">
                            <a href="{{ url('blog/'.$a->dash_title) }}" class="btn btn-xs btn-outline btn-primary">Read
                                More <i class="fa fa-long-arrow-right"></i> </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endsection
@section('script')
<script>
    $(document).ready(function () {
        $(".img-blog").css({'height' : $(".img-blog")[0].width/2});
    });
    $(window).on('resize', function(){
        $(".img-blog").css({'height' : $(".img-blog")[0].width/2});
    });
</script>
@endsection