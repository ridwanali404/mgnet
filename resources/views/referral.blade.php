@extends('layout.app')
@section('title', 'Referral')
@section('style')
    <link href="{{ asset('material-pro/assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('material-pro/assets/plugins/bootstrap-select/bootstrap-select.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">
    <style>
        .dt-bootstrap4 {
            padding: 0 !important;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid" ng-controller="UnilevelCtrl">
        <div class="row page-titles">
            <div class="col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Referral</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Referral</li>
                </ol>
            </div>
            <div class="col-4 align-self-center">
                <div class="d-flex justify-content-end">
                    <div class="d-flex ml-2">
                        @if (count(request()->input()) == 0)
                            <a href="{{ route('user.create') }}" class="btn btn-danger btn-rounded"><i
                                    class="fas fa-user-plus m-r-10"></i>
                                Registrasi</a>
                        @endif
                        @if (request()->get('username'))
                            <a href="javascript:history.back()" class="btn btn-danger btn-rounded"><i
                                    class="fas fa-arrow-left m-r-10"></i>
                                Kembali</a>
                        @endif
                        @if (request()->get('query'))
                            <a href="{{ url('referral') }}" class="btn btn-danger btn-rounded"><i
                                    class="fas fa-undo m-r-10"></i>
                                Reset</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <ul class="nav nav-pills p-3 bg-white mb-3 rounded-pill align-items-center"
            style="padding-left: 30px !important; padding-right: 30px !important;">
            <form action="{{ url('referral') }}" method="GET" class="form-material" style="width: 100%;">
                <div class="form-group" style="margin-bottom: 0;">
                    <input type="text" class="form-control" placeholder="Cari berdasarkan Username" name="query"
                        value="{{ request()->get('query') }}" minlength="3" style="background-image: none;">
                </div>
            </form>
        </ul>
        <div class="card">
            <div class="card-body">
                @if (!request()->get('query'))
                    <button class="float-right btn btn-sm btn-rounded btn-danger disabled">Level
                        {{ Auth::user()->level($user) }}</button>
                @endif
                <h4 class="card-title">Referral</h4>
                <div class="table-responsive">
                    <table id="table" class="display nowrap table table-hover table-striped table-bordered"
                        cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Dibuat pada</th>
                                <th>Username</th>
                                <th>Nama</th>
                                <th class="text-right">Referral</th>
                                <th class="text-center">Pin</th>
                                <th class="text-center">Masa Aktif</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $users = $user
                                    ->sponsors()
                                    ->oldest()
                                    ->get();
                                if (request()->get('query')) {
                                    $users = Auth::user()
                                        ->descendants()
                                        ->where('username', 'like', request()->get('query') . '%')
                                        ->orderBy('username')
                                        ->get();
                                }
                            @endphp
                            @foreach ($users as $a)
                                @php
                                    $activeUntil = $a->active_until;
                                    $isActive = $a->is_active;
                                    $daysLeft = null;
                                    
                                    if ($activeUntil && $isActive) {
                                        $now = \Carbon\Carbon::now();
                                        $activeDate = \Carbon\Carbon::parse($activeUntil);
                                        $daysLeft = floor($now->diffInDays($activeDate, false));
                                        
                                        // Hanya tampilkan jika masih aktif (daysLeft > 0)
                                        if ($daysLeft <= 0) {
                                            $daysLeft = null;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td><code>{{ $a->created_at }}</code></td>
                                    <td>
                                        <a href="{{ url('user/' . $a->id . '/profile') }}">{{ $a->username }}</a>
                                    </td>
                                    <td>{{ $a->name }}</td>
                                    <td class="text-right">
                                        <code>{{ number_format($a->sponsors()->count()) }}</code>
                                    </td>
                                    <td class="text-center"><code>{{ $a->userPin->pin->name_short ?? '' }}</code></td>
                                    <td class="text-center">
                                        @if ($daysLeft !== null && $daysLeft > 0)
                                            <code>{{ $daysLeft }} hari</code>
                                        @else
                                            <code>-</code>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a class="btn btn-xs btn-danger btn-rounded"
                                            href="{{ url('referral') . '?username=' . $a->username }}">
                                            detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <!-- This is data table -->
    <script src="{{ asset('material-pro/assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js') }}"></script>
    <script>
        jQuery(document).ready(function() {
            $('#table').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
                },
                "order": [
                    ["{{ request()->get('query') ? '2' : '1' }}", "asc"]
                ],
                "columnDefs": [{
                    "targets": [0, 7],
                    "orderable": false
                }]
            });
        });
    </script>
@endsection
