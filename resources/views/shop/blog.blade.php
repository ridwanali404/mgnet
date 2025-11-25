@extends('shop.layout.app')
@section('title', 'Blog')
@section('content')
<div class="container clearfix">

    <div class="fancy-title title-border mb-4 title-center">
        <h4>Blog</h4>
    </div>

    <div class="row">
        <div class="col-sm-12">

            <form action="{{ url('blog') }}" method="GET">
                <input type="text" name="query" value="{{ $query }}" placeholder="Cari blog..."
                    class="form-control sm-form-control border-0" />
            </form>
        </div>
    </div>

    <div class="row">
        @foreach( $blogs as $a )
        <div class="col-md-6">
            <div class="card">
                <img src="{{ asset($a->image_path) }}" class="card-img-top" alt="{{ $a->title }}">
                <div class="card-body">
                    <h5 class="card-title">{{ $a->title }}</h5>
                    <a href="{{ url('blog/'.$a->dash_title) }}" class="button nott fw-normal ms-1 my-0">Baca selengkapnya</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection