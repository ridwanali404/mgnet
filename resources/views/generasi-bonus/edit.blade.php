@extends('layout.app')
@section('title', 'Edit Bonus Generasi - ' . $pin->name)
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Edit Bonus Generasi – {{ $pin->name }}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('generasi-bonus.index') }}">Bonus Generasi</a></li>
                    <li class="breadcrumb-item active">{{ $pin->name }}</li>
                </ol>
            </div>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('generasi-bonus.update', $pin) }}" method="POST" class="form-material">
                            @csrf
                            @method('PUT')
                            <p class="text-muted">Masukkan nominal (IDR) per generasi. Isian harus angka bulat non-negatif.</p>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Generasi</th>
                                            @for ($g = 1; $g <= 10; $g++)
                                                <th class="text-center">Gen {{ $g }}</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Nominal (Rp)</strong></td>
                                            @for ($g = 1; $g <= 10; $g++)
                                                <td>
                                                    <input type="number" name="amount_{{ $g }}" class="form-control text-right"
                                                           value="{{ old('amount_' . $g, $amounts[$g] ?? 0) }}" min="0" step="1" required>
                                                </td>
                                            @endfor
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="{{ route('generasi-bonus.index') }}" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
