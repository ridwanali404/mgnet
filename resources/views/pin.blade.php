@extends('layout.app')
@section('title', 'Pin')
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Pin</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Pin</li>
                </ol>
            </div>
            <div class="col-md-7 col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end">
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
                                    <th>Nama</th>
                                    <th class="text-right">Harga</th>
                                    <th>Jenis</th>
                                    <th class="text-right" data-sort-ignore="true">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pins as $key => $a)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $a->name }}</td>
                                        <td class="text-right"><code>Rp {{ number_format($a->price) }}</code></td>
                                        <td><code>{{ $a->type }}</code></td>
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
    @foreach ($pins as $a)
        <div class="modal inmodal" id="edit{{ $a->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content animated fadeInDown">
                    <form action="{{ url('pin/' . $a->id) }}" method="POST">
                        @csrf
                        {{ method_field('PUT') }}
                        <div class="modal-body">
                            <h3>Ubah</h3>
                            <hr />
                            <div class="row">
                                <div class="form-group col-lg-6">
                                    <label>Nama</label>
                                    <div class="input-group m-b">
                                        <input type="text" name="name" value="{{ $a->name }}"
                                            class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="form-group col-lg-6">
                                    <label>Harga</label>
                                    <div class=" input-group m-b">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" name="price" value="{{ $a->price }}" min="0"
                                            step="100" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="checkbox" name="type" id="type-{{ $a->id }}" value="premium"
                                    {{ $a->type == 'premium' ? 'checked' : '' }}>
                                <label for="type-{{ $a->id }}">Premium</label>

                            </div>

                            <div class="form-group">
                                <div>
                                    <input name="type" type="radio" id="type-free-{{ $a->id }}" value="free"
                                        class="radio-col-red" {{ $a->type == 'free' ? 'checked' : '' }}>
                                    <label for="type-free-{{ $a->id }}">Free</label>
                                </div>
                                <div>
                                    <input name="type" type="radio" id="type-premium-{{ $a->id }}"
                                        value="premium" class="radio-col-red" {{ $a->type == 'premium' ? 'checked' : '' }}>
                                    <label for="type-premium-{{ $a->id }}">Premium</label>
                                </div>
                                <div>
                                    <input name="type" type="radio" id="type-upgrade-{{ $a->id }}"
                                        value="upgrade" class="radio-col-red" {{ $a->type == 'upgrade' ? 'checked' : '' }}>
                                    <label for="type-upgrade-{{ $a->id }}">Upgrade</label>
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
                    <form action="{{ url('pin/' . $a->id) }}" method="POST">
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
@section('script')
    <script>
        jQuery(document).ready(function() {
            $("form").submit(function() {
                $(this).find(":submit").prop('disabled', true);
            });
        });
    </script>
@endsection
