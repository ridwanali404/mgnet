@extends('shop.layout.app')
@section('title', 'Galeri')
@section('style')
<link href="{{ asset('inspinia/css/plugins/blueimp/css/blueimp-gallery.min.css') }}" rel="stylesheet">
<style>
    .lightBoxGallery {
        text-align: center;
    }
    .lightBoxGallery img {
        margin: 5px;
    }
</style>
@endsection
@section('content')
<div class="container clearfix">

    <div class="fancy-title title-border mb-4 title-center">
        <h4>Galeri</h4>
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