@extends('marketplace.layouts.inspinia')
@section('title')
{{ $blog->title }}
@endsection
@section('style')
@endsection
@section('content')
<section class="container animated fadeInRight article">
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="text-center article-title">
                        <span class="text-muted"><i class="fa fa-clock-o"></i>
                            {{ date('D d M, Y', strtotime($blog->created_at)) }}</span>
                        <h1>
                            {{ $blog->title }}
                        </h1>
                    </div>
                    @if($blog->image)
                    <!-- <p align="center">
                            <img src="{{ asset($blog->image_path) }}" style="max-width : 100%; height: auto;">
                        </p> -->
                    @endif
                    {!! $blog->content !!}
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="small text-right">
                                <div> Created at - <i class="fa fa-clock-o"> </i> {{ $blog->created_at }} </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('script')
@endsection