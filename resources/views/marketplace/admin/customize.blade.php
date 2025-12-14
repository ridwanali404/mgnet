@extends('marketplace.layouts.admin')
@section('title')
    General
@endsection
@section('style')
    <link href="{{ asset('bootstrap-imageupload/dist/css/bootstrap-imageupload.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="ibox">
        <div class="ibox-title">
            <h5>General</h5>
        </div>
        <div class="ibox-content">
            <form class="form-horizontal" action="{{ url('a/customize/' . $customize->id) }}" method="POST"
                enctype="multipart/form-data" onsubmit="update.disabled = true;">
                @csrf
                {{ method_field('PUT') }}
                <div class="form-group">
                    <label class="col-sm-2 control-label">Title</label>
                    <div class="col-sm-10">
                        <input type="text" name="title" value="{{ $customize->title }}" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Meta Description</label>
                    <div class="col-sm-10">
                        <input type="text" name="meta_description" value="{{ $customize->meta_description }}"
                            class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Meta Keywords</label>
                    <div class="col-sm-10">
                        <input type="text" name="meta_keywords" value="{{ $customize->meta_keywords }}"
                            class="form-control">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Favicon</label>
                    <div class="col-sm-10">
                        <div class="imageupload">
                            <div class="file-tab">
                                <label class="btn btn-default btn-file">
                                    <span>Browse</span>
                                    <!-- The file is stored here. -->
                                    <input type="file" name="image" accept="image/png">
                                </label>
                                <button type="button" class="btn btn-default">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-2">
                        <button class="btn btn-primary" type="submit" name="update">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="ibox">
        <div class="ibox-title">
            <h5>Public</h5>
        </div>
        <div class="ibox-content">
            <form class="form-horizontal" action="{{ url('key-value') }}" method="POST" enctype="multipart/form-data"
                onsubmit="update.disabled = true;">
                @csrf
                <div class="form-group">
                    <label class="col-sm-2 control-label">Banner Title</label>
                    <div class="col-sm-10">
                        <input type="text" name="banner_title"
                            value="{{ \App\Models\KeyValue::where('key', 'banner_title')->value('value') }}"
                            class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Banner Subtitle</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" rows="5" name="banner_subtitle">{!! \App\Models\KeyValue::where('key', 'banner_subtitle')->value('value') !!}</textarea>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Testimony</label>
                    <div class="col-sm-10">
                        <input type="text" name="testimony"
                            value="{{ \App\Models\KeyValue::where('key', 'testimony')->value('value') }}"
                            class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Testimony Text</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" rows="5" name="testimony_text">{!! \App\Models\KeyValue::where('key', 'testimony_text')->value('value') !!}</textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Testimony Footer</label>
                    <div class="col-sm-10">
                        <input type="text" name="testimony_footer"
                            value="{{ \App\Models\KeyValue::where('key', 'testimony_footer')->value('value') }}"
                            class="form-control">
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Gambar Coming Soon</label>
                    <div class="col-sm-10">
                        @php
                            $comingSoonImage = \App\Models\KeyValue::where('key', 'coming_soon_image')->value('value');
                        @endphp
                        @if($comingSoonImage)
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $comingSoonImage) }}" alt="Coming Soon Image" 
                                     style="max-width: 300px; max-height: 300px; margin-bottom: 10px;" class="img-thumbnail">
                                <br>
                                <small class="text-muted">Gambar saat ini</small>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="delete_coming_soon_image" value="1">
                                    Hapus gambar Coming Soon
                                </label>
                            </div>
                            <div class="hr-line-dashed"></div>
                        @endif
                        <input type="file" name="coming_soon_image" accept="image/*" class="form-control">
                        <small class="help-block m-t-xs">
                            Upload gambar untuk footer Coming Soon. Jika tidak diisi, tidak akan ada gambar yang ditampilkan.
                            <br>
                            <a href="{{ asset('images/hand_cr.png') }}" download="coming-soon-default.png" class="text-primary">
                                <i class="fa fa-download"></i> Download gambar default Coming Soon
                            </a>
                        </small>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-2">
                        <button class="btn btn-primary" type="submit" name="update">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('bootstrap-imageupload/dist/js/bootstrap-imageupload.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.imageupload').imageupload({
                maxFileSizeKb: 1024
            });
        });
    </script>
@endsection
