@extends('layout.app')
@section('title', 'Semua Member')
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
                <h3 class="text-themecolor m-b-0 m-t-0">Member</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Member</li>
                </ol>
            </div>
            <div class="col-4 align-self-center">
                <div class="d-flex justify-content-end">
                    <div class="d-flex ml-2">
                        <a href="#filter" data-toggle="modal" class="btn btn-danger btn-rounded"><i
                                class="fas fa-filter m-r-10"></i>
                            Filter</a>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-pills p-3 bg-white mb-3 rounded-pill align-items-center"
            style="padding-left: 30px !important; padding-right: 30px !important;">
            <form action="{{ url('users') }}" method="GET" class="form-material" style="width: 100%;">
                <div class="form-group" style="margin-bottom: 0;">
                    @if (request()->rank)
                        <input type="hidden" name="rank" value="{{ request()->rank }}">
                    @endif
                    <input type="text" class="form-control" placeholder="Cari berdasarkan Username atau Nama Member"
                        name="username" value="{{ request()->username }}" style="background-image: none;">
                </div>
            </form>
        </ul>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Member ({{ number_format($users->total()) }})</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Dibuat pada</th>
                                <th>Username</th>
                                <th>Nama</th>
                                <th class="text-right">Referral</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Peringkat</th>
                                <th class="text-center">Pin</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $key => $a)
                                <tr>
                                    <td>{{ $users->firstItem() + $key }}</td>
                                    <td><code>{{ $a->created_at }}</code></td>
                                    <td>{{ $a->username }}</td>
                                    <td>{{ $a->name }}</td>
                                    <td class="text-right">
                                        <code>{{ number_format($a->user ? $a->user->sponsors()->count() : 0) }}</code>
                                    </td>
                                    <td class="text-center">
                                        <code>{{ $a->phase == 'Free User' ? 'Free' : 'Mitra' }}</code>
                                    </td>
                                    <td class="text-center"><code>{{ $a->phase }}</code></td>
                                    <td class="text-center"><code>{{ $a->userPin->pin->name_short ?? '' }}</code>
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
                    <div class="float-lg-right">
                        {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal inmodal" id="filter" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated fadeInDown">
                <form action="{{ url('users') }}" method="GET" onsubmit="filter.disabled = true;">
                    @if (request()->username)
                        <input type="hidden" name="username" value="{{ request()->username }}">
                    @endif
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Filter</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Peringkat</label>
                            <select class="custom-select" name="rank">
                                <option value="">Semua Peringkat</option>
                                <option {{ request()->rank == 'User Free' ? 'selected' : '' }}>User Free</option>
                                <option {{ request()->rank == 'User' ? 'selected' : '' }}>User</option>
                                <option {{ request()->rank == 'User Q' ? 'selected' : '' }}>User Q</option>
                                <option {{ request()->rank == 'Star Seller' ? 'selected' : '' }}>Star Seller</option>
                                <option {{ request()->rank == 'Reseller' ? 'selected' : '' }}>Reseller</option>
                                <option {{ request()->rank == 'Agen' ? 'selected' : '' }}>Agen</option>
                                <option {{ request()->rank == 'Distributor' ? 'selected' : '' }}>Distributor</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="filter" class="btn btn-info">Filter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <!-- This is data table -->
    <script src="{{ asset('material-pro/assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js') }}"></script>
@endsection
