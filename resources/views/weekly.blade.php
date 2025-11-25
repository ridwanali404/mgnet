@extends('layout.app')
@section('title', 'Bonus Mingguan')
@section('style')
    <link href="{{ asset('material-pro/assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('material-pro/assets/plugins/bootstrap-select/bootstrap-select.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">
    <link rel="stylesheet" href="//cdn.datatables.net/select/1.3.3/css/select.dataTables.min.css">
    <style>
        .dt-bootstrap4 {
            padding: 0 !important;
        }
    </style>
@endsection
@php
    $week = request()->week ?? date('Y-\WW');
@endphp
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Bonus Mingguan</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Bonus Mingguan</li>
                </ol>
            </div>
        </div>
        <form class="form-group" id="filter" method="GET" action="{{ url('weekly') }}">
            <input class="form-control" type="week" name="week" value="{{ $week }}" id="week">
            <span class="help-block text-muted">
                <small>
                    Periode {{ \Carbon\Carbon::parse($week)->startofweek()->translatedFormat('l, j F Y') }} s.d.
                    {{ \Carbon\Carbon::parse($week)->endofweek()->translatedFormat('l, j F Y') }}
                </small>
            </span>
        </form>

        @if (Auth::user()->type == 'admin')
            <div class="card">
                <div class="card-body">
                    <a href="{{ url('daily') }}" class="float-right btn btn-sm btn-rounded btn-danger">Rekap Harian</a>
                    <button id="bulk" class="float-right btn btn-sm btn-rounded btn-success mr-2 d-none"
                        data-toggle="modal" data-target="#confirm">Konfirmasi Sekaligus</button>
                    <h4 class="card-title">Bonus Mingguan</h4>
                    <div class="table-responsive">
                        <table id="bonuses" class="display nowrap table table-hover table-striped table-bordered"
                            cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-square"></i></th>
                                    <th data-orderable=false>#</th>
                                    <th>Join</th>
                                    <th>Member</th>
                                    <th>Rekening</th>
                                    <th class="text-right">Komisi Pasangan (Rp)</th>
                                    <th class="text-right">Bonus Generasi (Rp)</th>
                                    <th class="text-right">Total (Rp)</th>
                                    <th class="text-right">Automaintain (Rp)</th>
                                    <th class="text-right">Pajak (Rp)</th>
                                    <th class="text-right">Biaya Admin (Rp)</th>
                                    <th class="text-right">Ditransfer (Rp)</th>
                                    <th>Status</th>
                                    <th>Dibayar pada</th>
                                    <th class="text-right">Konfirmasi</th>
                                    <th class="d-none">User ID</th>
                                    <th class="d-none">Payable</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $a)
                                    @php
                                        $this_week_paid = $a->weeklyBonuses($week)->first()->paid_at;
                                        $weeks = $this_week_paid
                                            ? $a
                                                ->bonuses()
                                                ->where('paid_at', $this_week_paid)
                                                ->get()
                                            : $a->unpaidWeeklyBonuses($week)->get();
                                        $weeks_group = $weeks
                                            ->groupBy(function ($item) {
                                                return $item->created_at->format('Y-\WW');
                                            })
                                            ->map(function ($group) use ($weekly_admin_fee) {
                                                $group_amount = $group->sum('amount');
                                                return (object) [
                                                    'week' => $group->first()->created_at->format('Y-\WW'),
                                                    'amount' => $group_amount,
                                                    'admin' => $group_amount >= 60000 ? $weekly_admin_fee : 0,
                                                    'paid_at' => $group->first()->paid_at,
                                                ];
                                            });
                                        $weeks_count = $weeks_group->count();
                                        $weeks_amount = $weeks->sum('amount');
                                        $total = $weeks_amount;
                                        $automaintain = round(0.1 * $total);
                                        $tax = $automaintain >= 330000 ? ($tax = ($automaintain * ($a->npwp ? 5 : 6)) / 100) : 0;
                                        $admin = $weeks_group->sum('admin');
                                        $total_transfer = $total - $automaintain - $tax - $admin;
                                    @endphp
                                    <tr>
                                        <td></td>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td><code>{{ $a->created_at->format('Y-m-d') }}</code></td>
                                        <td>
                                            <a href="{{ url('user/' . $a->id . '/profile') }}">
                                                {{ $a->username }}
                                            </a>
                                        </td>
                                        <td>
                                            @if ($a->bank)
                                                {{ $a->bank_as }}<br>
                                                <small>
                                                    <code>
                                                        {{ $a->bank->name }} {{ $a->bank->code }}
                                                        <strong>{{ $a->bank_account }}</strong>
                                                    </code>
                                                </small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <code>{{ number_format($a->weeklyBonuses($week)->where('type', 'Komisi Pasangan')->sum('amount')) }}</code>
                                        </td>
                                        <td class="text-right">
                                            <code>{{ number_format($a->weeklyBonuses($week)->where('type', 'Bonus Generasi')->sum('amount')) }}</code>
                                        </td>
                                        <td class="text-right">
                                            <code>{{ number_format($total) }}</code>
                                            @if ($weeks_count > 1)
                                                <br /><small><a href="#detail{{ $a->id }}" data-toggle="modal">Lihat
                                                        minggu</a></small>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <code>{{ number_format($automaintain) }}</code>
                                        </td>
                                        <td class="text-right">
                                            <code>{{ number_format($tax) }}</code>
                                        </td>
                                        <td class="text-right">
                                            <code>{{ number_format($admin) }}</code>
                                        </td>
                                        <td class="text-right">
                                            <code>{{ number_format($total_transfer) }}</code>
                                        </td>
                                        <td>
                                            @if ($this_week_paid)
                                                <span class="label label-success">Sudah dibayar</span>
                                            @elseif ($total_transfer < 50000)
                                                <span class="label label-danger">Bonus belum cukup</span>
                                            @else
                                                <span class="label label-warning">Menunggu pembayaran</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($this_week_paid)
                                                <code>{{ $this_week_paid }}</code>
                                                @if ($weeks_count > 1)
                                                    <br />
                                                    <small>
                                                        <a href="#detail-paid{{ $a->id }}"
                                                            data-toggle="modal">Dibayar sekaligus</a>
                                                    </small>
                                                @endif
                                            @else
                                                <code>-</code>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if (!$this_week_paid && $total_transfer >= 50000)
                                                <button class="btn btn-xs btn-rounded btn-success" data-toggle="modal"
                                                    data-target="#confirm{{ $a->id }}">konfirmasi</button><br />
                                                <code>Rp&nbsp;{{ number_format($total_transfer) }}</code><br />
                                                <small>Total Belum Ditransfer </small>
                                            @endif
                                            @if ($this_week_paid)
                                                <button class="btn btn-xs btn-rounded btn-danger" data-toggle="modal"
                                                    data-target="#cancel{{ $a->id }}">batalkan</button>
                                            @endif
                                        </td>
                                        <td class="d-none">{{ $a->id }}</td>
                                        <td class="d-none">{{ !$this_week_paid && $total_transfer >= 50000 ? 1 : 0 }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            @if ($bonuses->count())
                @php
                    $a = Auth::user();
                    $pending = $a
                        ->bonuses()
                        ->where('type', 'Bonus Generasi (Pending)')
                        ->get();
                    $isWeekActive = $a->isWeekActive($week);
                    $isPasangan = $a->userPin->pin->level > 2;
                @endphp
                @if ($pending->count())
                    <div class="alert alert-warning">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span
                                aria-hidden="true">×</span> </button>
                        <h3 class="text-warning"><i class="fa fa-exclamation-triangle"></i> Bonus Pending</h3>
                        Anda memiliki Bonus Pending dari Bonus Generasi, segera aktifkan Generasi untuk
                        mendapatkan bonus sejumlah
                        <strong>Rp {{ number_format($pending->sum('amount'), 0, ',', '.') }}</strong>.
                    </div>
                @endif
                @if ($a->userPin->pin->level > 2 && !$isWeekActive && false)
                    <div class="alert alert-warning">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span
                                aria-hidden="true">×</span> </button>
                        <h3 class="text-warning"><i class="fa fa-exclamation-triangle"></i> Fast Track Belum Aktif</h3>
                        Anda belum bisa mendapatkan Bonus Pasangan dan Bonus Generasi minggu ini, segera aktifkan
                        Fast
                        Track dengan belanja Produk Fast Track.
                        @php
                            $unpaidWeeklyBonusesSum = $a->unpaidWeeklyBonusesSum($week);
                        @endphp
                        @if ($unpaidWeeklyBonusesSum)
                            <br>
                            Saldo Bonus Fast Track Anda telah mencapai Rp
                            {{ number_format($unpaidWeeklyBonusesSum, 0, ',', '.') }}, gunakan Rp 750.000 untuk
                            mengaktifkan
                            Fast Track dengan klik tombol Aktifkan Fast Track.
                            <br>
                            <button class="btn btn-sm btn-rounded btn-danger mt-2" data-toggle="modal"
                                data-target=".activate">Aktifkan Fast Track</button>
                            <div class="modal fade activate">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form class="form-material" action="{{ url('weekly/activate') }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="week" value="{{ $week }}" />
                                            <div class="modal-body">
                                                <h4>Aktivasi Fast Track</h4>
                                                <p>
                                                    Apakah Anda yakin?
                                                    <br>
                                                    <small class="text-danger">Saldo Bonus Fast Track Anda akan terpotong
                                                        sebesar Rp 750.000.</small>
                                                </p>
                                                <p class="text-right">
                                                    <button type="submit"
                                                        class="btn btn-danger waves-effect">Aktifkan</button>
                                                </p>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
                @php
                    $this_week_paid = $a->weeklyBonuses($week)->first()->paid_at;
                    $weeks = $this_week_paid
                        ? $a
                            ->bonuses()
                            ->where('paid_at', $this_week_paid)
                            ->get()
                        : $a->unpaidWeeklyBonuses($week)->get();
                    $weeks_group = $weeks
                        ->groupBy(function ($item) {
                            return $item->created_at->format('Y-\WW');
                        })
                        ->map(function ($group) use ($weekly_admin_fee) {
                            $group_amount = $group->sum('amount');
                            return (object) [
                                'week' => $group->first()->created_at->format('Y-\WW'),
                                'amount' => $group_amount,
                                'admin' => $group_amount >= 60000 ? $weekly_admin_fee : 0,
                                'paid_at' => $group->first()->paid_at,
                            ];
                        });
                    $weeks_count = $weeks_group->count();
                    $weeks_amount = $weeks->sum('amount');
                    $total = $weeks_amount;
                    $automaintain = round(0.1 * $total);
                    $tax = $automaintain >= 330000 ? ($tax = ($automaintain * ($a->npwp ? 5 : 6)) / 100) : 0;
                    $admin = $weeks_group->sum('admin');
                    $total_transfer = $total - $automaintain - $tax - $admin;
                @endphp
                <div class="card table-responsive">
                    <table class="table table-hover table-stripped m-0">
                        <tr>
                            <td {{ !$isPasangan || !$isWeekActive ? 'class=text-danger' : '' }}>
                                Komisi Pasangan (Rp)
                                @if (!$isPasangan)
                                    <br><small class="text-muted">Belum Upgrade Gold atau Platinum</small>
                                @endif
                                @if (!$isWeekActive)
                                    <br><small class="text-muted">Fast Track Belum Aktif</small>
                                @endif
                            </td>
                            <td class="text-right">
                                <code>{{ number_format($bonuses->where('type', 'Komisi Pasangan')->sum('amount')) }}</code>
                            </td>
                        </tr>
                        <tr>
                            <td {{ !$isWeekActive ? 'class=text-danger' : '' }}>
                                Bonus Generasi (Rp)
                                @if (!$isWeekActive)
                                    <br><small class="text-muted">Fast Track Belum Aktif</small>
                                @endif
                            </td>
                            <td class="text-right">
                                <code>{{ number_format($bonuses->where('type', 'Bonus Generasi')->sum('amount')) }}</code>
                            </td>
                        </tr>
                        <tr>
                            <td>Total (Rp)</td>
                            <td class="text-right">
                                <code>{{ number_format($total) }}</code>
                                @if ($weeks_count > 1)
                                    <br /><small><a href="#detail{{ $a->id }}" data-toggle="modal">Lihat
                                            minggu</a></small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Automaintain (Rp)</td>
                            <td class="text-right">
                                <code>{{ number_format($automaintain) }}</code>
                            </td>
                        </tr>
                        <tr>
                            <td>Pajak (Rp)</td>
                            <td class="text-right">
                                <code>{{ number_format($tax) }}</code>
                            </td>
                        </tr>
                        <tr>
                            <td>Biaya Admin (Rp)</td>
                            <td class="text-right">
                                <code>{{ number_format($admin) }}</code>
                            </td>
                        </tr>
                        <tr>
                            <td>Total Ditransfer (Rp)</td>
                            <td class="text-right">
                                <code>{{ number_format($total_transfer) }}</code>
                            </td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td class="text-right">
                                @if ($this_week_paid)
                                    <span class="label label-success">Sudah dibayar</span>
                                @else
                                    <span class="label label-warning">Menunggu pembayaran</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Dibayar pada</td>
                            <td class="text-right">
                                @if ($this_week_paid)
                                    <code>{{ $this_week_paid }}</code>
                                    @if ($weeks_count > 1)
                                        <br />
                                        <small>
                                            <a href="#detail-paid{{ $a->id }}" data-toggle="modal">Dibayar
                                                sekaligus</a>
                                        </small>
                                    @endif
                                @else
                                    <code>-</code>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            @endif
            <div class="card">
                <div class="card-body">
                    <a href="{{ url('daily') }}" class="float-right btn btn-sm btn-rounded btn-danger">Rekap Harian</a>
                    <h4 class="card-title">Detail Bonus</h4>
                    <div class="table-responsive">
                        <table id="weeklyUnilevelBonuses"
                            class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                            width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tanggal</th>
                                    <th>Member</th>
                                    <th class="text-right">Bonus (Rp)</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bonuses as $a)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td><code>{{ $a->created_at }}</code></td>
                                        <td>{{ $a->user->username }}</td>
                                        <td class="text-right"><code>{{ number_format($a->amount) }}</code></td>
                                        <td>{{ $a->description }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        @if (Auth::user()->type == 'admin' || $bonuses->count())
            @php
                if (Auth::user()->type != 'admin') {
                    if ($bonuses->count()) {
                        $users = \App\Models\User::where('id', Auth::id())->get();
                    } else {
                        $users = \App\Models\User::whereNull('id')->get();
                    }
                }
            @endphp
            @foreach ($users as $a)
                @php
                    $this_week_paid = $a->weeklyBonuses($week)->first()->paid_at;
                    $weeks = $this_week_paid
                        ? $a
                            ->bonuses()
                            ->where('paid_at', $this_week_paid)
                            ->get()
                        : $a->unpaidWeeklyBonuses($week)->get();
                    $weeks_group = $weeks
                        ->groupBy(function ($item) {
                            return $item->created_at->format('Y-\WW');
                        })
                        ->map(function ($group) use ($weekly_admin_fee) {
                            $group_amount = $group->sum('amount');
                            return (object) [
                                'week' => $group->first()->created_at->format('Y-\WW'),
                                'amount' => $group_amount,
                                'admin' => $group_amount >= 60000 ? $weekly_admin_fee : 0,
                                'paid_at' => $group->first()->paid_at,
                            ];
                        });
                    $weeks_count = $weeks_group->count();
                    $weeks_amount = $weeks->sum('amount');
                    $total = $weeks_amount;
                    $automaintain = round(0.1 * $total);
                    $tax = $automaintain >= 330000 ? ($tax = ($automaintain * ($a->npwp ? 5 : 6)) / 100) : 0;
                    $admin = $weeks_group->sum('admin');
                    $total_transfer = $total - $automaintain - $tax - $admin;
                @endphp
                @if (Auth::user()->type == 'admin')
                    @if ($loop->first)
                        <form action="{{ url('weekly/confirm') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="week" value="{{ $week }}" />
                            <input type="hidden" name="user_ids" />
                            <div class="modal inmodal" id="confirm" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content animated fadeInDown">
                                        <div class="modal-body">
                                            <h3>Konfirmasi Sekaligus</h3>
                                            <p>Apakah anda yakin?</p>
                                            <div class="text-right">
                                                <button type="submit"
                                                    class="btn btn-success btn-rounded">Konfirmasi</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                    @if (!$this_week_paid && $total_transfer >= 50000)
                        <form action="{{ url('weekly/' . $a->id . '/confirm') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="week" value="{{ $week }}" />
                            <div class="modal inmodal" id="confirm{{ $a->id }}" tabindex="-1" role="dialog"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content animated fadeInDown">
                                        <div class="modal-body">
                                            <h3>Konfirmasi</h3>
                                            <p>Apakah anda yakin?</p>
                                            <div class="text-right">
                                                <button type="submit"
                                                    class="btn btn-success btn-rounded">Konfirmasi</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                    @if ($this_week_paid)
                        <form action="{{ url('weekly/' . $a->id . '/cancel') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="paid_at" value="{{ $this_week_paid }}" />
                            <div class="modal inmodal" id="cancel{{ $a->id }}" tabindex="-1" role="dialog"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content animated fadeInDown">
                                        <div class="modal-body">
                                            <h3>Batal</h3>
                                            <p>Apakah anda yakin?</p>
                                            <div class="text-right">
                                                <button type="submit"
                                                    class="btn btn-danger btn-rounded">Batalkan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                @endif
                @if ($weeks_count > 1)
                    <div class="modal inmodal" id="detail{{ $a->id }}" tabindex="-1" role="dialog"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content animated fadeInDown">
                                <div class="modal-header">
                                    <h4 class="modal-title">Detail Minggu</h4>
                                    <button type="button" class="close" data-dismiss="modal"><span
                                            aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                </div>
                                <div class="modal-body p-0">
                                    <div class="table-responsive">
                                        <table class="display nowrap table table-hover m-b-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Minggu</th>
                                                    <th class="text-right">Bonus (Rp)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($weeks_group as $group)
                                                    <tr>
                                                        <td>{{ $loop->index + 1 }}</td>
                                                        <td><code>{{ $group->week }}</code></td>
                                                        <td class="text-right">
                                                            <code>{{ number_format($group->amount) }}</code>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="2">
                                                        Total<br>
                                                        <small class="text-danger">Total bonus belum dipotong pajak dan
                                                            admin</small>
                                                    </th>
                                                    <th class="text-right">
                                                        <code>{{ number_format($total) }}</code>
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @if ($this_week_paid && $weeks_count > 1)
                    <div class="modal inmodal" id="detail-paid{{ $a->id }}" tabindex="-1" role="dialog"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content animated fadeInDown">
                                <div class="modal-header">
                                    <h4 class="modal-title">Detail Minggu</h4>
                                    <button type="button" class="close" data-dismiss="modal"><span
                                            aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                                </div>
                                <div class="modal-body p-0">
                                    <div class="table-responsive">
                                        <table class="display nowrap table table-hover m-b-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Tanggal Bonus</th>
                                                    <th class="text-right">Bonus (Rp)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($weeks_group as $group)
                                                    <tr>
                                                        <td>{{ $loop->index + 1 }}</td>
                                                        <td><code>{{ $group->week }}</code></td>
                                                        <td class="text-right">
                                                            <code>{{ number_format($group->amount) }}</code>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="2">Total</th>
                                                    <th class="text-right">
                                                        <code>{{ number_format($total) }}</code>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2">Pajak</th>
                                                    <th class="text-right">
                                                        <code>{{ number_format($tax) }}</code>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2">Biaya Admin</th>
                                                    <th class="text-right">
                                                        <code>{{ number_format($admin) }}</code>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2">Total
                                                        Ditransfer<br /><small><code>Dibayar pada
                                                                {{ $weeks->first()->paid_at }}</code></small>
                                                    </th>
                                                    <th class="text-right">
                                                        <code>{{ number_format($total_transfer) }}</code>
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
@endsection
@section('script')
    <!-- This is data table -->
    <script src="{{ asset('material-pro/assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js') }}"></script>
    <script src="//cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script>
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
            $("#week").on('change', function() {
                document.getElementById("filter").submit();
            });
            var bonuses = $('#bonuses').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                order: [
                    [2, 'asc']
                ],
                select: {
                    style: 'multi',
                },
                columnDefs: [{
                    className: 'select-checkbox',
                    targets: 0,
                    orderable: false,
                    searchable: false,
                    createdCell: function(td, cellData, rowData, row, col) {
                        if (cellData < 1 && rowData[16] == '0') {
                            $(td).removeClass('select-checkbox');
                        }
                    }
                }],
                createdRow: function(row, data, dataIndex) {
                    if (data[16] == '0') {
                        $(row).addClass('row-disabled');
                    }
                },
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json",
                    select: {
                        rows: {
                            _: "%d item dipilih",
                            0: "",
                        }
                    }
                },
            });
            bonuses.on('select deselect', function(e, dt, type, indexes) {
                if (type === 'row') {
                    var payable = bonuses.row(indexes).data()[16];
                    if (e.type == 'select' && payable == '0') {
                        bonuses.rows('.row-disabled', {
                            selected: true
                        }).deselect();
                    } else if (payable == '1') {
                        var data = bonuses.rows({
                            selected: true
                        }).data();
                        var user_ids = [];
                        data.each(function(row) {
                            user_ids.push(row[15]);
                        });
                        $('input[name=user_ids]').val(user_ids);
                        if (user_ids.length) {
                            $('#bulk').removeClass('d-none');
                        } else {
                            $('#bulk').addClass('d-none');
                        }
                    }
                }
            });
            $('#weeklyUnilevelBonuses').DataTable({
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
