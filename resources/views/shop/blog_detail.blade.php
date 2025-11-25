@extends('shop.layout.app')
@section('title', $blog->title)
@section('style')
@endsection
@section('content')
<div class="container clearfix">

    <div class="fancy-title title-border mb-4 title-center">
        <h4>{{ $blog->title }}</h4>
    </div>

    <div class="ibox">
        <div class="ibox-content">
            <div class="text-center article-title mb-4">
                <span class="text-muted"><i class="fa fa-clock-o"></i>{{ date('D d M, Y', strtotime($blog->created_at)) }}</span>
            </div>
            @if($blog->image)
            <p class="text-center">
                <img src="{{ asset($blog->image_path) }}" style="max-width: 100%;">
            </p>
            @endif
            {!! $blog->content !!}
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <div class="small text-right">
                        <div> Dibuat pada - <i class="fa fa-clock-o"> </i> {{ $blog->created_at }} </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
@section('script')
@endsection