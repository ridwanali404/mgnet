@extends('marketplace.layouts.inspinia')
@section('title')
    Home
@endsection
@section('content')
    <section id="categories" class="container services animated fadeInDown">
        <div class="row m-b-lg">
            <div class="col-lg-12 text-center">
                <div class="navy-line"></div>
                <h1>Our Categories</h1>
                <!-- <p>Donec sed odio dui. Etiam porta sem malesuada magna mollis euismod.</p> -->
                <p>These are our categories for you.</p>
            </div>
        </div>
        <div class="row">
            @foreach ($categories as $a)
                <div class="col-md-3 col-sm-6 col-xs-12 wow fadeInUp">
                    <div class="ibox">
                        <div class="ibox-content product-box">
                            <img class="img-category" src="{{ asset($a->image_path) }}" />
                            <!-- <div class="product-imitation">
                                        [ INFO ]
                                    </div> -->
                            <div class="product-desc">
                                <a href="#" class="product-name text-truncate"> {{ $a->name }}</a>
                                <!-- <div class="small m-t-xs">
                                            Many desktop publishing packages and web page editors now.
                                        </div> -->
                                <div class="m-t text-righ">
                                    <form action="{{ url('product') }}" method="GET">
                                        @if (isset($query))
                                            <input type="hidden" name="query" value="{{ $query }}" />
                                        @endif
                                        <input type="hidden" name="category" value="{{ $a->dash_name }}" />
                                        <button type="submit" class="btn btn-xs btn-outline btn-primary">See products <i
                                                class="fa fa-long-arrow-right"></i> </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section id="about-us" class="gray-section team">
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
                            <p style="white-space: pre-line">{{ $about_us->text }}</p>
                            <a href="{{ url('about-us') }}" class="btn btn-primary" style="margin-bottom: 10px;">Learn
                                more</a>
                        </div>
                        <div class="col-sm-6 text-right wow fadeInRight">
                            @if ($about_us->video)
                                <figure>
                                    <iframe width="457" height="289" src="{{ $about_us->video }}" frameborder="0"
                                        allowfullscreen style="max-width: 100%"></iframe>
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
                    <form action="{{ url('product') }}" method="GET">
                        <input type="text" name="query" placeholder="Search product..." class="form-control"
                            style="border: none" />
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach ($products as $a)
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
                                <a href="{{ url('product/' . $a->dash_name) }}" class="product-name text-truncate">
                                    {{ $a->name }}</a>
                                <!-- <div class="small m-t-xs">
                                            Many desktop publishing packages and web page editors now.
                                        </div> -->
                                <div class="m-t text-righ">
                                    <a href="{{ url('product/' . $a->dash_name) }}"
                                        class="btn btn-xs btn-outline btn-primary">Info <i
                                            class="fa fa-long-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="pull-right wow fadeInUp" style="margin-bottom: 20px">
            <a href="{{ url('product') }}" class="btn btn-outline btn-primary">See more products...</a>
        </p>
    </section>

    <section id="testimonials" class="navy-section testimonials" style="margin-top: 0">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center wow zoomIn">
                    <i class="fa fa-comment big-icon"></i>
                    <h1>{{ \App\Models\KeyValue::where('key', 'testimony')->value('value') }}</h1>
                    <div class="testimonials-text">
                        <i style="white-space: pre-line">
                            {{ \App\Models\KeyValue::where('key', 'testimony_text')->value('value') }}
                        </i>
                    </div>
                    <small>
                        <strong>{{ \App\Models\KeyValue::where('key', 'testimony_footer')->value('value') }}</strong>
                    </small>
                </div>
            </div>
        </div>
    </section>

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
                        <input type="text" name="query" placeholder="Search blog..." class="form-control"
                            style="border: none" />
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach ($blogs as $a)
                <div class="col-md-6 wow fadeInUp">
                    <div class="ibox">
                        <div class="ibox-content product-box">
                            <img class="img-blog" src="{{ asset($a->image_path) }}" />
                            <div class="product-desc">
                                <a href="{{ url('blog/' . $a->dash_title) }}"
                                    class="product-name text-truncate">{{ $a->title }}</a>
                                <div class="small m-t-xs">
                                    <span class="fa fa-clock-o fa-fw"></span>{{ $a->created_at }}<br />
                                    <!-- Many desktop publishing packages and web page editors now. -->
                                </div>
                                <div class="m-t text-righ">
                                    <a href="{{ url('blog/' . $a->dash_title) }}"
                                        class="btn btn-xs btn-outline btn-primary">Read
                                        More <i class="fa fa-long-arrow-right"></i> </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="pull-right wow fadeInUp" style="margin-bottom: 20px">
            <a href="{{ url('blog') }}" class="btn btn-outline btn-primary">See more blogs...</a>
        </p>
    </section>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $(".img-category").css({
                'height': $(".img-category")[0].width
            });
            $(".img-product").css({
                'height': $(".img-product")[0].width
            });
            $(".img-blog").css({
                'height': $(".img-blog")[0].width / 2
            });
        });
        $(window).on('resize', function() {
            $(".img-category").css({
                'height': $(".img-category")[0].width
            });
            $(".img-product").css({
                'height': $(".img-product")[0].width
            });
            $(".img-blog").css({
                'height': $(".img-blog")[0].width / 2
            });
        });
    </script>
@endsection
