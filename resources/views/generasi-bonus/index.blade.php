@extends('layout.app')
@section('title', 'Bonus Generasi per Paket')
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Bonus Generasi per Paket</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Bonus Generasi</li>
                </ol>
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('fail'))
            <div class="alert alert-danger">{{ session('fail') }}</div>
        @endif
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted">Ubah nominal bonus generasi (Gen 1–10) per paket. Nilai ini dipakai saat join/upgrade/RO.</p>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>Paket</th>
                                        <th>Harga</th>
                                        <th class="text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pins as $pin)
                                        <tr>
                                            <td><strong>{{ $pin->name }}</strong></td>
                                            <td>Rp {{ number_format($pin->price, 0, ',', '.') }}</td>
                                            <td class="text-right">
                                                <a href="{{ route('generasi-bonus.edit', $pin) }}" class="btn btn-sm btn-primary">Edit Nominal</a>
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
    </div>
@endsection
