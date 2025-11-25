@extends('layout.app')
@section('title', 'Peringkat')
@section('style')
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
@php
    $date = request()->date ?? date('Y-m-d');
@endphp
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Peringkat</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Peringkat</li>
                </ol>
            </div>
            <div class="col-md-7 col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end">
                </div>
            </div>
        </div>
        @if (Auth::user()->type == 'member')
            @php
                $rank_ids = Auth::user()
                    ->userRanks()
                    ->pluck('rank_id');
                $ranks = \App\Models\Rank::orderBy('nominal')->get();
                $cash = Auth::user()->cash_rank;
                $nextRank = \App\Models\Rank::orderBy('nominal')
                    ->whereNotIn('id', $rank_ids)
                    ->first();
                if (!$nextRank) {
                    $nextRank = \App\Models\Rank::orderBy('nominal', 'desc')->first();
                }
                $percentage = round(($cash / $nextRank->nominal) * 100, 2);
                $percentage = min($percentage, 100);
            @endphp
            <div class="card table-responsive">
                <table class="table table-hover table-stripped m-0">
                    <thead>
                        <tr>
                            <th class="text-center">Cash (Rp)</th>
                            <th class="text-center">Progress</th>
                            <th class="text-center">Nominal Selanjutnya (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">{{ number_format($cash, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <h5 class="d-flex flex-wrap"><span>{{ number_format($cash, 0, ',', '.') }} /
                                        {{ number_format($nextRank->nominal, 0, ',', '.') }}</span><span
                                        class="ml-auto">{{ $percentage }}%</span></h5>
                                <div class="progress">
                                    <div class="progress-bar active progress-bar-striped bg-danger"
                                        style="width: {{ $percentage }}%; height:6px;" role="progressbar"></div>
                                </div>
                            </td>
                            <td class="text-center">{{ number_format($nextRank->nominal, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card table-responsive">
                <table class="table table-hover table-stripped m-0">
                    <thead>
                        <tr>
                            <th class="text-center">Nominal (Rp)</th>
                            <th class="text-center">Peringkat</th>
                            <th class="text-center">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ranks as $key => $a)
                            @php
                                $userRank = Auth::user()
                                    ->userRanks()
                                    ->where('rank_id', $a->id)
                                    ->first();
                            @endphp
                            <tr>
                                <td class="text-center">{{ number_format($a->nominal, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $a->rank }}</td>
                                <td class="text-center">
                                    @if ($userRank)
                                        <span class="label label-success">Sudah tercapai</span>
                                    @else
                                        <span class="label label-warning">Belum tercapai</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Peringkat</h3>
                <h6 class="card-subtitle">Histori peringkat</h6>
                <div class="table-responsive">
                    <table id="userRank" class="display nowrap table table-hover table-striped table-bordered"
                        cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tercapai pada</th>
                                <th>Member</th>
                                <th>Peringkat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userRanks as $a)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td><code>{{ $a->created_at }}</code></td>
                                    <td>{{ $a->user->username }}</td>
                                    <td>{{ $a->rank->rank }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <h3 class="card-title">Histori Bonus</h3>
            <h6 class="card-subtitle">Histori bonus harian dan mingguan</h6>
        </div>
        <form class="form-group" method="GET" action="{{ url('userRank') }}">
            <input class="form-control" type="date" name="date" value="{{ $date }}" min="2023-06-04"
                onchange="this.form.submit()">
        </form>
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Histori Bonus</h3>
                <h6 class="card-subtitle">Histori bonus harian dan mingguan</h6>
                <div class="table-responsive">
                    <table id="userRankBonuses" class="display nowrap table table-hover table-striped table-bordered"
                        cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Member</th>
                                <th class="text-right">Bonus (Rp)</th>
                                <th>Deskripsi</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userRankBonuses as $a)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td><code>{{ $a->created_at }}</code></td>
                                    <td>{{ $a->user->username }}</td>
                                    <td class="text-right"><code>{{ number_format($a->amount, 0, ',', '.') }}</code></td>
                                    <td>{{ $a->description }}</td>
                                    <td class="text-right">
                                        @if (Auth::user()->type == 'admin')
                                            <a class="btn btn-xs btn-rounded btn-success"
                                                href="{{ route('userRank.index', ['date' => \Carbon\Carbon::parse($a->created_at)->format('Y-m-d'), 'username' => $a->user->username]) }}">detail</a><br />
                                        @else
                                            <a class="btn btn-xs btn-rounded btn-success"
                                                href="{{ route('userRank.index', ['date' => \Carbon\Carbon::parse($a->created_at)->format('Y-m-d')]) }}">detail</a><br />
                                        @endif
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
    <!-- start - This is for export functionality only -->
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
    <!-- end - This is for export functionality only -->
    <script>
        jQuery(document).ready(function() {
            $('#userRank').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
            $('#userRankHistories').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
            $('#userRankBonuses').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
        });
    </script>
@endsection
