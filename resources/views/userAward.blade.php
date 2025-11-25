@extends('layout.app')
@section('title', 'Reward')
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
                </div>
            </div>
        </div>
        @if (Auth::user()->type == 'member')
            @php
                $award_ids = Auth::user()
                    ->userAwards()
                    ->pluck('award_id');
                $awards = \App\Models\Award::orderBy('nominal')->get();
                $cash = Auth::user()->cash_award;
                $nextAward = \App\Models\Award::orderBy('nominal')
                    ->whereNotIn('id', $award_ids)
                    ->first();
                if (!$nextAward) {
                    $nextAward = \App\Models\Award::orderBy('nominal', 'desc')->first();
                }
                $percentage = round(($cash / $nextAward->nominal) * 100, 2);
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
                                        {{ number_format($nextAward->nominal, 0, ',', '.') }}</span><span
                                        class="ml-auto">{{ $percentage }}%</span></h5>
                                <div class="progress">
                                    <div class="progress-bar active progress-bar-striped bg-danger"
                                        style="width: {{ $percentage }}%; height:6px;" role="progressbar"></div>
                                </div>
                            </td>
                            <td class="text-center">{{ number_format($nextAward->nominal, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card table-responsive">
                <table class="table table-hover table-stripped m-0">
                    <thead>
                        <tr>
                            <th class="text-center">Nominal (Rp)</th>
                            <th class="text-center">Reward</th>
                            <th class="text-center">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($awards as $key => $a)
                            @php
                                $userAward = Auth::user()
                                    ->userAwards()
                                    ->where('award_id', $a->id)
                                    ->first();
                            @endphp
                            <tr>
                                <td class="text-center">{{ number_format($a->nominal, 0, ',', '.') }}</td>
                                <td class="text-center">{{ $a->award }}</td>
                                <td class="text-center">
                                    @if ($userAward)
                                        @if ($userAward->is_paid)
                                            <span class="label label-success">Sudah dikonfirmasi</span>
                                        @else
                                            <span class="label label-warning">Menunggu konfirmasi</span>
                                        @endif
                                    @elseif($cash < $a->nominal)
                                        Jumlah Cash belum mencukupi.
                                    @elseif($cash >= $a->nominal)
                                        @php
                                            $before = \App\Models\Award::where('nominal', '<', $a->nominal)->pluck('id');
                                            $claimed = Auth::user()
                                                ->userAwards()
                                                ->whereIn('award_id', $before)
                                                ->count();
                                        @endphp
                                        <button class="btn btn-xs btn-rounded btn-success" data-toggle="modal"
                                            data-target="#claim{{ $a->id }}"
                                            {{ $claimed != $before->count() ? 'disabled' : '' }}>klaim</button><br />
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
                <h3 class="card-title">Reward</h3>
                <h6 class="card-subtitle">Reward menunggu konfirmasi</h6>
                <div class="table-responsive">
                    <table id="userAward" class="display nowrap table table-hover table-striped table-bordered"
                        cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Dibuat pada</th>
                                <th>Member</th>
                                <th>Reward</th>
                                @if (Auth::user()->type == 'admin')
                                    <th class="text-right">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userAwards as $a)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td><code>{{ $a->created_at }}</code></td>
                                    <td>{{ $a->user->username }}</td>
                                    <td>{{ $a->award->award }}</td>
                                    @if (Auth::user()->type == 'admin')
                                        <td class="text-right">
                                            <button class="btn btn-xs btn-rounded btn-success" data-toggle="modal"
                                                data-target="#confirm{{ $a->id }}">konfirmasi</button><br />
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Histori Reward</h3>
                <h6 class="card-subtitle">Reward telah dikonfirmasi</h6>
                <div class="table-responsive">
                    <table id="userAwardHistories" class="display nowrap table table-hover table-striped table-bordered"
                        cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Dibuat pada</th>
                                <th>Member</th>
                                <th>Reward</th>
                                <th>Dikonfirmasi pada</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userAwardHistories as $a)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td><code>{{ $a->created_at }}</code></td>
                                    <td>{{ $a->user->username }}</td>
                                    <td>{{ $a->award->award }}</td>
                                    <td><code>{{ $a->updated_at }}</code></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <h3 class="card-title">Histori Poin</h3>
            <h6 class="card-subtitle">Histori poin pasangan reward</h6>
        </div>
        <form class="form-group" method="GET" action="{{ url('userAward') }}">
            <input class="form-control" type="date" name="date" value="{{ $date }}" min="2023-06-04"
                onchange="this.form.submit()">
        </form>
        @if (Auth::user()->type == 'admin')
            <div class="card table-responsive">
                <table class="table table-hover table-stripped m-0">
                    <thead>
                        <tr style="line-height: 1.3;">
                            <th class="text-center">Poin Pasangan<br><small class="text-muted">Jumlah poin pasangan
                                    reward</small>
                            </th>
                            <th class="text-center">Pasangan<br><small class="text-muted">Jumlah pasangan reward
                                    tercipta</small>
                            </th>
                            <th class="text-center">Nilai (Rp)<br><small class="text-muted">Hasil nilai 1 unit
                                    pasangan reward</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center" id="poin">
                                <div class="spinner-grow spinner-grow-sm mb-1" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </td>
                            <td class="text-center" id="pair">
                                <div class="spinner-grow spinner-grow-sm mb-1" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </td>
                            <td class="text-center" id="value">
                                <div class="spinner-grow spinner-grow-sm mb-1" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Histori Poin Reward</h3>
                <h6 class="card-subtitle">Histori poin award masuk</h6>
                <div class="table-responsive">
                    <table id="userAwardBonuses" class="display nowrap table table-hover table-striped table-bordered"
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
                            @foreach ($userAwardBonuses as $a)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td><code>{{ $a->created_at }}</code></td>
                                    <td>{{ $a->user->username }}</td>
                                    <td class="text-right"><code>{{ number_format($a->amount, 0, ',', '.') }}</code></td>
                                    <td>{{ $a->description }}</td>
                                    <td class="text-right">
                                        @if (Auth::user()->type == 'admin')
                                            <a class="btn btn-xs btn-rounded btn-success"
                                                href="{{ route('userAward.index', ['date' => \Carbon\Carbon::parse($a->created_at)->format('Y-m-d'), 'username' => $a->user->username]) }}">detail</a><br />
                                        @else
                                            <a class="btn btn-xs btn-rounded btn-success"
                                                href="{{ route('userAward.index', ['date' => \Carbon\Carbon::parse($a->created_at)->format('Y-m-d')]) }}">detail</a><br />
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if (Auth::user()->type != 'admin' || request()->username)
            @php
                $user = Auth::user()->type != 'admin' ? Auth::user() : \App\Models\User::where('username', request()->username)->first();
            @endphp
            @if ($user && $user->userPin->level >= 3)
                @php
                    $user = Auth::user();
                    $pr_dailyPoinSponsors = $user
                        ->dailyPoinSponsors()
                        ->where('date', $date)
                        ->orderBy('pr', 'desc')
                        ->get();
                    $pr_before_user = $user
                        ->dailyProfits()
                        ->where('date', '<', $date)
                        ->orderBy('date', 'desc')
                        ->first();
                    if ($pr_before_user && $pr_before_user->pr_id) {
                        if ($pr_dailyPoinSponsors->where('user_id', $pr_before_user->pr_id)->count()) {
                            $pr_dailyPoinSponsors = $pr_dailyPoinSponsors->map(function ($a) use ($pr_before_user) {
                                if ($a->user_id == $pr_before_user->pr_id) {
                                    $a->pr += $pr_before_user->pr_current;
                                    $a->is_before = true;
                                }
                                return $a;
                            });
                        } else {
                            $pr_dailyPoinSponsors->push(
                                new App\Models\DailyPoin([
                                    'user_id' => $pr_before_user->pr_id,
                                    'pr' => $pr_before_user->pr_current,
                                ]),
                            );
                        }
                    }
                    $pr_l = 0;
                    $pr_r = 0;
                    $pr_select_before = 'r';
                    $pr_select_current = 'l';
                    $pr_dailyPoinSponsors = $pr_dailyPoinSponsors->sortByDesc('pr')->values();
                    $pr_id = $pr_dailyPoinSponsors->first()->user_id ?? null;
                @endphp
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Histori Poin Reward</h4>
                        <h6 class="card-subtitle">Potensi poin award</h6>
                        <ul class="timeline">
                            @foreach ($pr_dailyPoinSponsors as $dailyPoin)
                                @if ($pr_l <= $pr_r)
                                    @php
                                        $pr_l += $dailyPoin->pr;
                                        $is_left = true;
                                    @endphp
                                    @include('pr')
                                @else
                                    @php
                                        $pr_r += $dailyPoin->pr;
                                        $is_left = false;
                                    @endphp
                                    @include('pr')
                                @endif
                            @endforeach
                        </ul>
                        <table class="table table-stripped m-0">
                            <tr>
                                <td class="text-center">{{ number_format($pr_l, 0, ',', '.') }} Poin</td>
                                <td class="text-center">{{ number_format($pr_r, 0, ',', '.') }} Poin</td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>
    @if (Auth::user()->type == 'admin')
        @foreach ($userAwards as $a)
            <form action="{{ url('userAward/' . $a->id . '/confirm') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal inmodal" id="confirm{{ $a->id }}" tabindex="-1" role="dialog"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content animated fadeInDown">
                            <div class="modal-body">
                                <h3>Konfirmasi</h3>
                                <p>Apakah anda yakin?</p>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-success btn-rounded">Konfirmasi</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        @endforeach
    @else
        @foreach ($awards as $a)
            @if ($cash >= $a->nominal)
                @php
                    $before = \App\Models\Award::where('nominal', '<', $a->nominal)->pluck('id');
                    $claimed = Auth::user()
                        ->userAwards()
                        ->whereIn('award_id', $before)
                        ->count();
                @endphp
                @if ($claimed == $before->count())
                    <form action="{{ url('userAward/' . $a->id . '/claim') }}" method="POST"
                        onsubmit="claim{{ $a->id }}.disabled = true;">
                        @csrf
                        @method('PUT')
                        <div class="modal inmodal" id="claim{{ $a->id }}" tabindex="-1" role="dialog"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content animated fadeInDown">
                                    <div class="modal-body">
                                        <h3>Klaim</h3>
                                        <p>Apakah anda yakin?</p>
                                        <div class="text-right">
                                            <button type="submit" name="claim{{ $a->id }}"
                                                class="btn btn-success btn-rounded">Klaim</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif
            @endif
        @endforeach
    @endif
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
            $('#userAward').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
            $('#userAwardHistories').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });
            $('#userAwardBonuses').DataTable({
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
    @if (Auth::user()->type == 'admin' && !env('LOCAL'))
        <script>
            $(document).ready(function() {
                $.get("daily/pair-reward?date={{ $date }}", function(data, status) {
                    if (status == 'success') {
                        $('#poin').html(data.pr);
                        $('#pair').html(data.pair);
                        $('#value').html(data.value);
                    } else {
                        $('#poin').html('Data gagal dimuat');
                        $('#pair').html('Data gagal dimuat');
                        $('#value').html('Data gagal dimuat');
                    }
                }).fail(function() {
                    $('#poin').html('Data gagal dimuat');
                    $('#pair').html('Data gagal dimuat');
                    $('#value').html('Data gagal dimuat');
                });
            });
        </script>
    @endif
@endsection
