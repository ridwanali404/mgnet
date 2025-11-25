@extends('marketplace.layouts.admin')
@section('title')
Dashboard
@endsection
@section('style')
<link href="{{ asset('inspinia/css/plugins/chosen/bootstrap-chosen.css') }}" rel="stylesheet">
<!-- SUMMERNOTE -->
<link href="{{ asset('inspinia/css/plugins/summernote/summernote.css') }}" rel="stylesheet">
<link href="{{ asset('inspinia/css/plugins/summernote/summernote-bs3.css') }}" rel="stylesheet">
<link href="{{ asset('bootstrap-imageupload/dist/css/bootstrap-imageupload.min.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="ibox">
    <div class="ibox-title">
        <h5>New Product</h5>
    </div>
    <div class="ibox-content">
        <form class="form-horizontal" action="{{ url('a/product') }}" method="POST" enctype="multipart/form-data"
            onsubmit="add.disabled = true;">
            @csrf
            <div class="form-group">
                <label class="col-sm-2 control-label">Name:</label>
                <div class="col-sm-10">
                    <input type="text" name="name" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Category:</label>
                <div class="col-sm-10">
                    <select data-placeholder="Choose a category..." class="form-control chosen-select"
                        name="category_id" tabindex="2">
                        @foreach ($categories as $a)
                        <option value="{{ $a->id }}">{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Price:</label>
                <div class="col-sm-10">
                    <div class="input-group">
                        <span class="input-group-addon">Rp</span>
                        <input type="number" name="price" min="1" max="1000000000" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Description:</label>
                <div class="col-sm-10">
                    <textarea class="form-control summernote" name="content"></textarea>
                </div>
            </div>
            <div class="hr-line-dashed"></div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Quantity:</label>
                <div class="col-sm-10">
                    <input type="number" name="qty" min="1" max="1000000000" class="form-control" required novalidate>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Sold:</label>
                <div class="col-sm-10">
                    <input type="number" name="sold" min="1" max="1000000000" class="form-control" required novalidate>
                </div>
            </div>
            <div class="hr-line-dashed"></div>
            @for ($i = 1; $i <= 3; $i++) <div class="form-group">
                <label class="col-sm-2 control-label">Image {{ $i }}</label>
                <div class="col-sm-10">
                    <div class="imageupload">
                        <div class="file-tab">
                            <label class="btn btn-default btn-file">
                                <span>Browse</span>
                                <!-- The file is stored here. -->
                                <input type="file" name="image{{ $i }}">
                            </label>
                            <button type="button" class="btn btn-default">Remove</button>
                        </div>
                    </div>
                </div>
    </div>
    @endfor
    <div class="hr-line-dashed"></div>
    <div class="form-group">
        <div class="col-sm-4 col-sm-offset-2">
            <button class="btn btn-primary" type="submit" name="add">Save product</button>
        </div>
    </div>
    </form>
</div>
</div>
@endsection
@section('script')
<!-- Chosen -->
<script src="{{ asset('inspinia/js/plugins/chosen/chosen.jquery.js') }}"></script>
<!-- SUMMERNOTE -->
<script src="{{ asset('inspinia/js/plugins/summernote/summernote.min.js') }}"></script>
<script src="{{ asset('bootstrap-imageupload/dist/js/bootstrap-imageupload.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.chosen-select').chosen({width: "100%"});
		$('.summernote').summernote();
		$('.imageupload').imageupload({
			maxFileSizeKb: 512
		});
    });
</script>
@endsection