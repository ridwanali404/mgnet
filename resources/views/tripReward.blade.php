@extends('layout.app')
@section('title', 'Trip Reward')
@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor m-b-0 m-t-0">Trip Reward</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="javascript:void(0)">Home</a>
                </li>
                <li class="breadcrumb-item active">Trip Reward</li>
            </ol>
        </div>
        <div class="col-md-7 col-4 align-self-center">
            <div class="d-flex m-t-10 justify-content-end">
                <a href="#" class="btn waves-effect waves-light btn-danger pull-right" data-toggle="modal"
                    data-target="#add"> Buat Trip Reward</a>&nbsp;
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
                                <th>Nama Reward</th>
                                <th class="text-right">Nominal</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th class="text-right" data-sort-ignore="true">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tripRewards as $key => $a)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td><strong>{{ $a->name }}</strong></td>
                                <td class="text-right"><code>Rp {{ number_format($a->nominal, 0, ',', '.') }}</code></td>
                                <td>{{ $a->description ?? '-' }}</td>
                                <td>
                                    @if ($a->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="text-nowrap text-right">
                                    <a href="#" data-toggle="modal" data-target="#edit{{ $a->id }}"><i
                                            class="mdi mdi-pencil text-inverse"></i> </a>
                                    <a href="#" data-toggle="modal" data-target="#delete{{ $a->id }}"><i
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
            <form action="{{ url('trip-reward') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <h3>Buat Trip Reward</h3>
                    <hr />
                    <div class="form-group">
                        <label>Nama Reward <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Bali, Umroh" required>
                    </div>
                    <div class="form-group">
                        <label>Nominal <span class="text-danger">*</span></label>
                        <div class="input-group m-b">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" name="nominal" min="0" step="1000" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi reward (opsional)"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <input type="checkbox" value="1" name="is_active" id="is_active" checked>
                        <label for="is_active">Aktif<br><small>Reward dapat diklaim jika aktif.</small></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@foreach ($tripRewards as $a)
<div class="modal inmodal" id="edit{{ $a->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content animated fadeInDown">
            <form action="{{ url('trip-reward/'.$a->id) }}" method="POST">
                @csrf
                {{ method_field('PUT') }}
                <div class="modal-body">
                    <h3>Ubah Trip Reward</h3>
                    <hr />
                    <div class="form-group">
                        <label>Nama Reward <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ $a->name }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nominal <span class="text-danger">*</span></label>
                        <div class="input-group m-b">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" name="nominal" value="{{ $a->nominal }}" min="0" step="1000"
                                class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3">{{ $a->description }}</textarea>
                    </div>
                    <div class="form-group mb-0">
                        <input type="checkbox" value="1" name="is_active" id="is_active_{{ $a->id }}" {{ $a->is_active ? 'checked' : '' }}>
                        <label for="is_active_{{ $a->id }}">Aktif<br><small>Reward dapat diklaim jika aktif.</small></label>
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
            <form action="{{ url('trip-reward/'.$a->id) }}" method="POST">
                @csrf
                {{ method_field('DELETE') }}
                <div class="modal-body">
                    <h3>Hapus Trip Reward</h3>
                    <p>Apakah anda yakin ingin menghapus "{{ $a->name }}" (Rp {{ number_format($a->nominal, 0, ',', '.') }})?</p>
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
@section('script')
<script>
    jQuery(document).ready(function () {
		$("form").submit(function () {
			$(this).find(":submit").prop('disabled', true);
		});
	});
</script>
@endsection
