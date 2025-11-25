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
<style>
    a {
        outline: none !important;
    }
</style>
@endsection
@section('content')
<form action="{{ url('a/product/'.$product->id) }}" method="POST" enctype="multipart/form-data"
    onsubmit="update.disabled = true;">
    @csrf
    {{ method_field('PUT') }}
    <div class="tabs-container">
        <ul class="nav nav-tabs">
            <li class="active">
                <a data-toggle="tab" href="#tab-1"> Product info</a>
            </li>
            <li class="">
                <a data-toggle="tab" href="#tab-4"> Images</a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="tab-1" class="tab-pane active">
                <div class="panel-body">

                    <fieldset class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Name:</label>
                            <div class="col-sm-10">
                                <input type="text" name="name" value="{{ $product->name }}" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Category:</label>
                            <div class="col-sm-10">
                                <select data-placeholder="Choose a category..." class="form-control chosen-select"
                                    name="category_id" tabindex="2">
                                    @foreach ($categories as $a)
                                    <option value="{{ $a->id }}" @if ($product->category_id == $a->id) selected
                                        @endif>{{ $a->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Price:</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input type="number" name="price" value="{{ $product->price }}" min="1"
                                        max="1000000000" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Description:</label>
                            <div class="col-sm-10">
                                <textarea class="form-control summernote"
                                    name="content">{!! $product->content !!}</textarea>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Quantity:</label>
                            <div class="col-sm-10">
                                <input type="number" name="qty" value="{{ $product->qty }}" min="1" max="1000000000"
                                    class="form-control" required novalidate>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Sold:</label>
                            <div class="col-sm-10">
                                <input type="number" name="sold" value="{{ $product->sold }}" min="1" max="1000000000"
                                    class="form-control" required novalidate>
                            </div>
                        </div>
                    </fieldset>

                </div>
            </div>
            <div id="tab-4" class="tab-pane">
                <div class="panel-body">

                    <div class="table-responsive">
                        <table class="table table-bordered table-stripped">
                            <thead>
                                <tr>
                                    <th>
                                        Current Image
                                    </th>
                                    <th>
                                        New Image
                                    </th>
                                    <th>
                                        Sort order
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 1; $i <= 3; $i++) <tr>
                                    <td>
                                        @if($product->productImages()->where('number', $i)->first())
                                        <a href="#" type="button" data-toggle="modal" data-target=".image{{$i}}">
                                            <img class="img-thumbnail img-100"
                                                src="{{ asset($product->productImages()->where('number', $i)->first()->image) }}" />
                                        </a>
                                        <div class="modal inmodal image{{$i}}" role="dialog">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content animated fadeInDown">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal"><span
                                                                aria-hidden="true">&times;</span><span
                                                                class="sr-only">Close</span></button>
                                                        <i class="fa fa-picture-o modal-icon"></i>
                                                    </div>
                                                    <div class="modal-body">
                                                        <center>
                                                            <img src="{{ asset($product->productImages()->where('number', $i)->first()->image) }}"
                                                                class="img-thumbnail" />
                                                        </center>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-danger"
                                                            data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @else
                                        No Image
                                        @endif
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" value="{{ $i }}" readonly>
                                    </td>
                                    </tr>
                                    @endfor
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <button class="btn btn-primary m-t m-b" type="submit" name="update">Save changes</button>
</form>
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