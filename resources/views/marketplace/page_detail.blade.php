@extends('marketplace.layouts.inspinia')
@section('title')
{{ $page->name }}
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
                            {{ date('D d M, Y', strtotime($page->created_at)) }}</span>
                        <h1>
                            {{ $page->name }}
                        </h1>
                    </div>
                    {!! $page->content !!}
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="small text-right">
                                <div> Created at - <i class="fa fa-clock-o"> </i> {{ $page->created_at }} </div>
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