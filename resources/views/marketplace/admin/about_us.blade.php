@extends('marketplace.layouts.admin')
@section('title')
About Us
@endsection
@section('style')
<link href="{{ asset('bootstrap-imageupload/dist/css/bootstrap-imageupload.min.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="ibox">
    <div class="ibox-title">
        <h5>About Us</h5>
    </div>
    <div class="ibox-content">
        <form class="form-horizontal" action="{{ url('a/about-us/'.$about_us->id) }}" method="POST"
            enctype="multipart/form-data" onsubmit="update.disabled = true;">
            @csrf
            {{ method_field('PUT') }}
            <div class="form-group">
                <label class="col-sm-2 control-label">Title</label>
                <div class="col-sm-10">
                    <input type="text" name="title" value="{{ $about_us->title }}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Sub Title</label>
                <div class="col-sm-10">
                    <input type="text" name="sub_title" value="{{ $about_us->sub_title }}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Text</label>
                <div class="col-sm-10">
                    <textarea class="form-control" name="text" rows="5"
                        style="resize: vertical;">{{ $about_us->text }}</textarea>
                </div>
            </div>
            <div class="hr-line-dashed"></div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Image</label>
                <div class="col-sm-10">
                    <div class="imageupload">
                        <div class="file-tab">
                            <label class="btn btn-default btn-file">
                                <span>Browse</span>
                                <!-- The file is stored here. -->
                                <input type="file" name="image">
                            </label>
                            <button type="button" class="btn btn-default">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Video</label>
                <div class="col-sm-10">
                    <input type="text" name="video" value="{{ $about_us->video }}" class="form-control">
                    <span class="help-block m-b-none">YouTube embed link.</span>
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