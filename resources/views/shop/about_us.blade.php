@extends('shop.layout.app')
@section('title', 'Tentang Kami')
@section('style')
@endsection
@section('content')
<div class="container clearfix">

    <div class="fancy-title title-border mb-4 title-center">
        <h4>Tentang Kami</h4>
    </div>
    <div class="row" style="margin-bottom: 30px">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-sm-6 features-text wow fadeInLeft">
                    <small>{{ $about_us->title }}</small>
                    <h2>{{ $about_us->sub_title }} </h2>
                    <p style="white-space: pre-line">{!! $about_us->text !!}</p>
                </div>
                <div class="col-sm-6 text-right wow fadeInRight">
                    @if($about_us->video)
                    <figure>
                        <iframe width="457" height="289" src="{{ $about_us->video }}" frameborder="0"
                            allowfullscreen></iframe>
                    </figure>
                    @else
                    <img src="{{ $about_us->image_path }}" alt="dashboard" class="img-responsive pull-right">
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
@endsection