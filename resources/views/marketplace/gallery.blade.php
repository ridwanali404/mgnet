@extends('marketplace.layouts.inspinia')
@section('title')
Gallery
@endsection
@section('style')
<link href="{{ asset('inspinia/css/plugins/blueimp/css/blueimp-gallery.min.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="container">
    <div class="row m-b-lg">
        <div class="col-lg-12 text-center">
            <div class="navy-line"></div>
            <h1>Our Gallery</h1>
            <p>These are our galleries for you.</p>
        </div>
    </div>
    <div class="lightBoxGallery">
        @foreach ($galleries as $a)
        <a href="{{ asset($a->image_path) }}" title="Image from Unsplash" data-gallery=""><img
                src="{{ asset($a->image_path) }}" width="100px" height="100px" style="object-fit: cover"></a>
        @endforeach

        <!-- The Gallery as lightbox dialog, should be a child element of the document body -->
        <div id="blueimp-gallery" class="blueimp-gallery">
            <div class="slides"></div>
            <h3 class="title"></h3>
            <a class="prev">‹</a>
            <a class="next">›</a>
            <a class="close">×</a>
            <a class="play-pause"></a>
            <ol class="indicator"></ol>
        </div>

    </div>

</div>
@endsection
@section('script')
<!-- blueimp gallery -->
<script src="{{ asset('inspinia/js/plugins/blueimp/jquery.blueimp-gallery.min.js') }}"></script>
@endsection