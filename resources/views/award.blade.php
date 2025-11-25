@extends('layout.app')
@section('title', 'Reward')
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Reward</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Reward</li>
                </ol>
            </div>
            <div class="col-md-7 col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end">
                    <a href="#" class="btn waves-effect waves-light btn-danger pull-right" data-toggle="modal"
                        data-target="#add"> Buat Reward</a>&nbsp;
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-stripped m-b-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="text-right">Nominal</th>
                                    <th>Reward</th>
                                    <th class="text-right" data-sort-ignore="true">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($awards as $key => $a)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td class="text-right"><code>Rp {{ number_format($a->nominal) }}</code></td>
                                        <td>{{ $a->award }}</td>
                                        <td class="text-nowrap text-right">
                                            <a href="#" data-toggle="modal" data-target="#edit{{ $a->id }}"><i
                                                    class="mdi mdi-pencil text-inverse"></i> </a>
                                            <a href="#" data-toggle="modal"
                                                data-target="#delete{{ $a->id }}"><i
                                                    class="mdi mdi-delete text-danger ml-2"></i> </a>
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
    <div class="modal inmodal" id="add" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated fadeInDown">
                <form action="{{ url('award') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <h3>Buat</h3>
                        <hr />
                        <div class="form-group">
                            <label>Nominal</label>
                            <div class="input-group m-b">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input type="number" name="nominal" min="0" step="100" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Reward</label>
                            <div class="input-group m-b">
                                <input type="text" name="award" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @foreach ($awards as $a)
        <div class="modal inmodal" id="edit{{ $a->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content animated fadeInDown">
                    <form action="{{ url('award/' . $a->id) }}" method="POST">
                        @csrf
                        {{ method_field('PUT') }}
                        <div class="modal-body">
                            <h3>Ubah</h3>
                            <hr />
                            <div class="form-group">
                                <label>Nominal</label>
                                <div class=" input-group m-b">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" name="nominal" value="{{ $a->nominal }}" min="0"
                                        step="100" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Reward</label>
                                <div class="input-group m-b">
                                    <input type="text" name="award" value="{{ $a->award }}" class="form-control"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Simpan perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal inmodal" id="delete{{ $a->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content animated fadeInDown">
                    <form action="{{ url('award/' . $a->id) }}" method="POST">
                        @csrf
                        {{ method_field('DELETE') }}
                        <div class="modal-body">
                            <h3>Hapus</h3>
                            <p>Apakah anda yakin?</p>
                            <div class="text-right">
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
