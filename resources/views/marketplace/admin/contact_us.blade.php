@extends('marketplace.layouts.admin')
@section('title')
Contact Us
@endsection
@section('style')
@endsection
@section('content')
<div class="ibox">
    <div class="ibox-title">
        <h5>Contact Us</h5>
    </div>
    <div class="ibox-content">
        <form class="form-horizontal" action="{{ url('a/contact-us/'.$contact_us->id) }}" method="POST"
            enctype="multipart/form-data" onsubmit="update.disabled = true;">
            @csrf
            {{ method_field('PUT') }}
            <div class="form-group">
                <label class="col-md-2 control-label">Company Name</label>
                <div class="col-md-10">
                    <input type="text" name="company" value="{{ $contact_us->company }}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Address Line 1</label>
                <div class="col-md-10">
                    <input type="text" name="address_line_1" value="{{ $contact_us->address_line_1 }}"
                        class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Address Line 2</label>
                <div class="col-md-10">
                    <input type="text" name="address_line_2" value="{{ $contact_us->address_line_2 }}"
                        class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Text</label>
                <div class="col-md-10">
                    <textarea class="form-control" name="text" rows="5"
                        style="resize: vertical;">{{ $contact_us->text }}</textarea>
                </div>
            </div>
            <div class="hr-line-dashed"></div>
            <div class="form-group">
                <label class="col-md-2 control-label">Phone Number</label>
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-addon">+62</span>
                        <input type="text" name="phone" value="{{ $contact_us->phone }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Email</label>
                <div class="col-md-10">
                    <input type="email" name="email" value="{{ $contact_us->email }}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Instaram</label>
                <div class="col-md-10">
                    <input type="text" name="instagram" value="{{ $contact_us->instagram }}" class="form-control">
                    <span class="help-block m-b-none">Instagram link.</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Facebook</label>
                <div class="col-md-10">
                    <input type="text" name="facebook" value="{{ $contact_us->facebook }}" class="form-control">
                    <span class="help-block m-b-none">Facebook link.</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label">Youtube</label>
                <div class="col-md-10">
                    <input type="text" name="youtube" value="{{ $contact_us->youtube }}" class="form-control">
                    <span class="help-block m-b-none">Youtube link.</span>
                </div>
            </div>
            <div class="hr-line-dashed"></div>
            <div class="form-group">
                <div class="col-md-4 col-md-offset-2">
                    <button class="btn btn-primary" type="submit" name="update">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('script')
@endsection