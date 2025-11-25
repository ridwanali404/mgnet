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
