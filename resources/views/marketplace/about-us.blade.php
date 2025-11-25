@extends('marketplace.layouts.inspinia')
@section('title')
About Us
@endsection
@section('style')
@endsection
@section('content')
<section id="about-us" class="team">
    <div class="container">
        <div class="row m-b-lg">
            <div class="col-lg-12 text-center">
                <div class="navy-line"></div>
                <h1>About Us</h1>
                <p>You need to know who we are.</p>
            </div>
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
</section>
@endsection
@section('script')
@endsection