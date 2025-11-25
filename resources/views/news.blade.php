@extends('layout.app')
@section('title', 'Berita Member')
@section('style')
<!-- summernotes CSS -->
<link href="{{ asset('material-pro/assets/plugins/summernote/dist/summernote-bs4.css') }}" rel="stylesheet" />
<style>
    .right-side-toggle i {
        -webkit-animation-name: none;
        animation-name: none;
    }

    .note-editor.note-frame.card {
        margin-bottom: 0;
    }

    .note-form-label {
        width: 100%;
    }
</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor m-b-0 m-t-0">Berita Member</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="javascript:void(0)">Home</a>
                </li>
                <li class="breadcrumb-item active">Berita Member</li>
            </ol>
        </div>
        <div class="col-md-7 col-4 align-self-center">
            <div class="d-flex m-t-10 justify-content-end">
                <a href="#" class="btn waves-effect waves-light btn-danger pull-right" data-toggle="modal"
                    data-target="#add">
                    Buat Berita</a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-stripped m-b-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Judul</th>
                                <th>Isi</th>
                                <th>Youtube</th>
                                <th>Download</th>
                                <th class="text-right" data-sort-ignore="true">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($news as $a)
                            <tr>
                                <td>{{ $loop->index+1 }}</td>
                                <td><code>{{ $a->created_at }}</code></td>
                                <td>{{ $a->title }}</td>
                                <td class="wrap-text">{!! $a->content !!}</td>
                                <td class="text-nowrap">
                                    @if($a->youtube)
                                    <a href="#" data-toggle="modal" data-target="#youtube{{$loop->index}}"><i class="mdi mdi-youtube-play text-inverse"></i> </a>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if($a->file)
                                    <a href="{{ url('storage/'.$a->file) }}" target="_blank"><i class="mdi mdi-download text-inverse"></i> </a>
                                    @endif
                                </td>
                                <td class="text-nowrap text-right">
                                    <a href="#" data-toggle="modal" data-target="#edit{{$loop->index}}"><i
                                            class="mdi mdi-pencil text-inverse m-r-10"></i> </a>
                                    <a href="#" data-toggle="modal" data-target="#delete{{$loop->index}}"><i
                                            class="mdi mdi-delete text-danger"></i> </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@foreach ($news as $a)
@if($a->youtube)
<div class="modal inmodal text-left" id="youtube{{ $loop->index }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated fadeInDown">
            <form action="{{ url('news/'.$a->id) }}" method="POST">
                @csrf
                @method('put')
                <div class="modal-header">
                    <h4 class="modal-title">Youtube</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <iframe width="560" height="315" src="{{ $a->youtube }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-secondary">Tutup</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
<div class="modal inmodal text-left" id="edit{{ $loop->index }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated fadeInDown">
            <form action="{{ url('news/'.$a->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="modal-header">
                    <h4 class="modal-title">Edit Berita</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Judul</label>
                        <input name="title" value="{{ $a->title }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Isi</label>
                        <textarea class="form-control" rows="5" name="content">{{ $a->content }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Youtube</label>
                        <input name="youtube" value="{{ $a->youtube }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Download File</label>
                        <div class="custom-file mb-3">
                            <input type="file" class="custom-file-input" id="customFile" name="file">
                            <label class="custom-file-label form-control" for="customFile"></label>
                          </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Edit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal inmodal text-left" id="delete{{ $loop->index }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated fadeInDown">
            <form action="{{ url('news/'.$a->id) }}" method="POST">
                @csrf
                @method('delete')
                <div class="modal-header">
                    <h4 class="modal-title">Hapus Berita</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Apakah anda yakin akan menghapus Berita ini?
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
<div class="modal inmodal" id="add" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated fadeInDown">
            <form action="{{ url('news') }}" method="POST" enctype="multipart/form-data"
                onsubmit="addButton.disabled = true;">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Buat Berita</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Judul</label>
                        <input name="title" class="form-control" required>
                    </div>
                    {{-- <div class="form-group">
                        <label>Isi</label>
                        <textarea class="form-control" rows="5" name="content"></textarea>
                    </div> --}}
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" class="form-control summernote" rows="5"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Youtube</label>
                        <input name="youtube" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Download File</label>
                        <div class="custom-file mb-3">
                            <input type="file" class="custom-file-input" id="customFile" name="file">
                            <label class="custom-file-label form-control" for="customFile"></label>
                          </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="addButton" class="btn btn-info">Buat</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="{{ asset('material-pro/assets/plugins/summernote/dist/summernote-bs4.min.js') }}"></script>
<script>
    $(function () {
        $('.summernote').summernote({
            height: 350, // set editor height
            minHeight: null, // set minimum height of editor
            maxHeight: null, // set maximum height of editor
            focus: false, // set focus to editable area after initializing summernote
            dialogsInBody: true
        });
    });
</script>
@endsection