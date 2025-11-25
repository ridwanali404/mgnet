@extends('layout.app')
@section('title', 'Bonus Bulanan')
@section('style')
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">
    <link rel="stylesheet" href="//cdn.datatables.net/select/1.3.3/css/select.dataTables.min.css">
    <style>
        .dt-bootstrap4 {
            padding: 0 !important;
        }

        .spinner-border.spinner-border-sm {
            margin-bottom: 2px;
        }
    </style>
@endsection
@php
    $month = request()->month ?? date('Y-m');
@endphp
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Bonus Bulanan</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Bonus Bulanan</li>
                </ol>
            </div>
            <div class="col-md-7 col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end">
                    @if (Auth::user()->type == 'admin')
                        @if (!\App\Models\MonthlyClosing::whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->count())
                            <a href="#" class="btn waves-effect waves-light btn-danger pull-right" data-toggle="modal"
                                data-target=".closing"> Closing
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}</a>&nbsp;
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @if (!in_array(Auth::user()->type, ['admin', 'cradmin']))
            @if (Auth::user()->monthlyQualified($month))
                @if ($closing)
                    @php
                        $monthly_bonuses = Auth::user()
                            ->monthlyBonuses($month)
                            ->get();
                        $bonus = $monthly_bonuses->sum('amount');
                        $tax = 0;
                        $administrative = 0;
                        if ($bonus > 10000) {
                            $tax = 10000;
                        }
                        if ($bonus > 330000) {
                            if (Auth::user()->npwp) {
                                $administrative = $bonus * 0.05;
                            } else {
                                $administrative = $bonus * 0.06;
                            }
                        }
                        $bonus_total = $bonus - $tax - $administrative;
                    @endphp
                    @if ($bonus_total > 50000)
                        @if ($monthly_bonuses->first()->paid_at)
                            <div class="alert alert-success">Bonus bulan
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                sejumlah
                                Rp {{ number_format($bonus_total, 0, ',', '.') }}
                                telah ditransfer ke rekening Anda.
                            </div>
                        @else
                            <div class="alert alert-warning">Bonus bulan
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                sejumlah
                                Rp {{ number_format($bonus_total, 0, ',', '.') }}
                                sedang menunggu untuk ditransfer ke rekening Anda.
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">Bonus bulan
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                            sejumlah
                            Rp {{ number_format($bonus_total, 0, ',', '.') }}
                            akan ditransfer bulan selanjutnya apabila telah mencapai Rp 50.000.
                        </div>
                    @endif
                @else
                    <div class="alert alert-success">Selamat, anda sudah bisa mendapatkan bonus bulan
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}.
                    </div>
                @endif
            @else
                <div class="alert alert-warning">Anda belum bisa mendapatkan bonus bulan
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }},
                    silahkan lakukan belanja produk RO minimal sejumlah 39 PV. Poin anda saat ini sejumlah
                    {{ number_format(Auth::user()->monthlyPoin(date('Y-m'))) }} PV</div>
            @endif
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-row">
                                <div class="round round-lg align-self-center round-danger"><i class="mdi mdi-trophy"></i>
                                </div>
                                <div class="m-l-10 align-self-center">
                                    <h3 class="m-b-0 font-light">
                                        {{ Auth::user()->member->member_phase_name ?? 'Administrator' }}&nbsp;
                                    </h3>
                                    <h5 class="text-muted m-b-0">Peringkat</h5>
                                    <small>&nbsp;</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <a href="{{ url('transaction') }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-row">
                                    <div class="round round-lg align-self-center round-sucess"><i
                                            class="mdi mdi-cart-outline"></i>
                                    </div>
                                    <div class="m-l-10 align-self-center">
                                        <h3 class="m-b-0 font-light">
                                            {{ number_format(Auth::user()->paidTransaction($month)->sum('price'),0,',','.') }}
                                        </h3>
                                        <h5 class="text-muted m-b-0">Belanja produk</h5>
                                        <small>Klik untuk melihat detail</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-row">
                                <div class="round round-lg align-self-center round-warning"><i class="mdi mdi-coin"></i>
                                </div>
                                <div class="m-l-10 align-self-center">
                                    <h3 class="m-b-0 font-light">
                                        {{ Auth::user()->monthlyPoin($month) }}&nbsp;
                                    </h3>
                                    <h5 class="text-muted m-b-0">Poin Value</h5>
                                    <small>PV</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card table-responsive">
                <table class="table table-hover table-stripped m-0">
                    <tr>
                        <td>
                            Transaksi Marketplace
                            <div>
                                <small class="form-text text-muted">
                                    Total poin produk RO dari transaksi marketplace
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                </small>
                            </div>
                            <div>
                                <small class="form-text text-muted">
                                    <a href="#t" data-toggle="modal">klik untuk melihat detail</a>
                                </small>
                            </div>
                        </td>
                        <td class="text-right">
                            <code>
                                {{ number_format($t->sum('poin'), 0, ',', '.') }}
                            </code>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Transaksi Produk RO
                            <div>
                                <small class="form-text text-muted">
                                    Total poin produk RO dari transaksi langsung
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                </small>
                            </div>
                            <div>
                                <small class="form-text text-muted">
                                    <a href="#ot" data-toggle="modal">klik untuk melihat detail</a>
                                </small>
                            </div>
                        </td>
                        <td class="text-right">
                            <code>
                                {{ number_format($ot->sum('poin'), 0, ',', '.') }}
                            </code>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Transaksi TopUp Produk RO
                            <div>
                                <small class="form-text text-muted">
                                    Total poin produk RO dari transaksi langsung (topup)
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                </small>
                            </div>
                            <div>
                                <small class="form-text text-muted">
                                    <a href="#ott" data-toggle="modal">klik untuk melihat detail</a>
                                </small>
                            </div>
                        </td>
                        <td class="text-right">
                            <code>
                                {{ number_format($ott->sum('poin'), 0, ',', '.') }}
                            </code>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Upgrade Member
                            <div>
                                <small class="form-text text-muted">
                                    Total PV dari Upgrade Member
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                </small>
                            </div>
                            <div>
                                <small class="form-text text-muted">
                                    <a href="#dp" data-toggle="modal">klik untuk melihat detail</a>
                                </small>
                            </div>
                        </td>
                        <td class="text-right">
                            <code>
                                {{ number_format($dp->sum('pv'), 0, ',', '.') }}
                            </code>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Total</b></td>
                        <td class="text-right">
                            <code>
                                <b>{{ number_format($t->sum('poin') + $ot->sum('poin') + $ott->sum('poin') + $dp->sum('pv'), 0, ',', '.') }}</b>
                            </code>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="modal fade" id="t">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header d-flex align-items-center">
                            <h4 class="modal-title">Transaksi Marketplace</h4>
                            <button type="button" class="close ml-auto" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body p-0">
                            <table class="table table-hover table-stripped m-0">
                                <tr>
                                    <td>#</td>
                                    <td>Dibuat pada</td>
                                    <td>Username</td>
                                    <td class="text-right">Poin</td>
                                </tr>
                                @foreach ($t as $a)
                                    <tr>
                                        <td>
                                            {{ $loop->index + 1 }}
                                        </td>
                                        <td>
                                            <code>
                                                {{ $a->created_at }}
                                            </code>
                                        </td>
                                        <td>
                                            @if ($a->user)
                                                <a href="{{ url('user/' . $a->user->id . '/profile') }}">
                                                    {{ $a->user->username }}
                                                </a>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <code>
                                                {{ $a->poin }}
                                            </code>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="ot">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header d-flex align-items-center">
                            <h4 class="modal-title">Transaksi Produk RO</h4>
                            <button type="button" class="close ml-auto" data-dismiss="modal"
                                aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body p-0">
                            <table class="table table-hover table-stripped m-0">
                                <tr>
                                    <td>#</td>
                                    <td>Dibuat pada</td>
                                    <td>Username</td>
                                    <td class="text-right">Poin</td>
                                </tr>
                                @foreach ($ot as $a)
                                    <tr>
                                        <td>
                                            {{ $loop->index + 1 }}
                                        </td>
                                        <td>
                                            <code>
                                                {{ $a->created_at }}
                                            </code>
                                        </td>
                                        <td>
                                            @if ($a->user)
                                                <a href="{{ url('user/' . $a->user->id . '/profile') }}">
                                                    {{ $a->user->username }}
                                                </a>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <code>
                                                {{ $a->poin }}
                                            </code>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="ott">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header d-flex align-items-center">
                            <h4 class="modal-title">Transaksi Produk RO</h4>
                            <button type="button" class="close ml-auto" data-dismiss="modal"
                                aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body p-0">
                            <table class="table table-hover table-stripped m-0">
                                <tr>
                                    <td>#</td>
                                    <td>Dibuat pada</td>
                                    <td>Username</td>
                                    <td class="text-right">Poin</td>
                                </tr>
                                @foreach ($ott as $a)
                                    <tr>
                                        <td>
                                            {{ $loop->index + 1 }}
                                        </td>
                                        <td>
                                            <code>
                                                {{ $a->created_at }}
                                            </code>
                                        </td>
                                        <td>
                                            @if ($a->user)
                                                <a href="{{ url('user/' . $a->user->id . '/profile') }}">
                                                    {{ $a->user->username }}
                                                </a>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <code>
                                                {{ $a->poin }}
                                            </code>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="dp">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header d-flex align-items-center">
                            <h4 class="modal-title">Upgrade Member</h4>
                            <button type="button" class="close ml-auto" data-dismiss="modal"
                                aria-hidden="true">×</button>
                        </div>
                        <div class="modal-body p-0">
                            <table class="table table-hover table-stripped m-0">
                                <tr>
                                    <td>#</td>
                                    <td>Dibuat pada</td>
                                    <td>Username</td>
                                    <td class="text-right">Poin</td>
                                </tr>
                                @foreach ($dp as $a)
                                    <tr>
                                        <td>
                                            {{ $loop->index + 1 }}
                                        </td>
                                        <td>
                                            <code>
                                                {{ $a->date }}
                                            </code>
                                        </td>
                                        <td>
                                            @if ($a->user)
                                                <a href="{{ url('user/' . $a->user->id . '/profile') }}">
                                                    {{ $a->user->username }}
                                                </a>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <code>
                                                {{ $a->pv }}
                                            </code>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <form class="form-group" id="filter" method="GET" action="{{ url('monthly') }}">
            <input class="form-control" type="month" name="month" value="{{ $month }}" id="month">
        </form>
        <div class="row">
            <div class="col-12">
                @if (in_array(Auth::user()->type, ['admin', 'cradmin']))
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex no-block">
                                <div>
                                    <h4 class="card-title">Bonus Bulanan</h4>
                                    @if (!$closing)
                                        <h6 class="card-subtitle">
                                            Bonus Unilevel RO dan Royalti Profit Sharing akan diakumulasi setelah closing
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                        </h6>
                                    @endif
                                </div>
                                <div class="ml-auto">
                                    <button id="bulk" class=" btn btn-sm btn-rounded btn-success mr-2 d-none"
                                        data-toggle="modal" data-target="#confirm">Konfirmasi Sekaligus</button>
                                    <select id="table-filter" class="custom-select custom-select-sm"
                                        style="width: auto;">
                                        <option selected="" value="">Semua Status</option>
                                        <option>Harus dibayar</option>
                                        <option>Sudah dibayar</option>
                                        <option>Menunggu pembayaran</option>
                                        <option>Belum qualified</option>
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="monthly-bonuses"
                                    class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-square"></i></th>
                                            <th data-orderable=false>#</th>
                                            <th>Join</th>
                                            <th>Member</th>
                                            <th>Rekening</th>
                                            <th class="text-right">Komisi Penjualan (Rp)</th>
                                            <th class="text-right">Bonus Unilevel RO (Rp)</th>
                                            <th class="text-right">Bonus Royalti Profit Sharing 13% (Rp)</th>
                                            <th class="text-right">Total</th>
                                            <th class="text-right">Admin</th>
                                            <th class="text-right">Pajak</th>
                                            <th class="text-right">Ditransfer</th>
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
                                                $monthly_cashback_bonuses = $a->monthlyCashbackBonuses($month)->sum('amount');
                                                $monthly_unilevel_RO_bonuses = $a->monthlyUnilevelROBonuses($month)->sum('amount');
                                                $monthly_profit_sharing_13_bonuses = $a->monthlyProfitSharing13Bonuses($month)->sum('amount');
                                                $monthly_bonus = $a->monthlyBonuses($month)->first();
                                                $monthly_bonuses = $a->monthlyBonuses($month)->sum('amount');
                                                $monthly_qualified = $a->monthlyQualified($month);
                                                $monthly_total = $monthly_bonuses - ($monthly_bonuses > $monthly_admin_fee ? $monthly_admin_fee : 0) - ($monthly_bonuses > 330000 ? ($a->npwp ? ($monthly_bonuses * 5) / 100 : ($monthly_bonuses * 6) / 100) : 0);
                                                if (!$monthly_qualified) {
                                                    $status_html = '<span class="label label-danger">Belum qualified</span>';
                                                    $status = 'Belum qualified';
                                                } else {
                                                    if ($monthly_bonus->paid_at) {
                                                        $status_html = '<span class="label label-primary">Sudah dibayar</span>';
                                                        $status = 'Sudah dibayar';
                                                    } elseif ($monthly_total >= 50000) {
                                                        $status_html = '<span class="label label-warning">Harus dibayar</span>';
                                                        $status = 'Harus dibayar';
                                                    } else {
                                                        $status_html = '<span class="label label-info">Menunggu pembayaran</span>';
                                                        $status = 'Menunggu pembayaran';
                                                    }
                                                }
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
                                                    <code>{{ number_format($monthly_cashback_bonuses, 0, ',', '.') }}</code>
                                                </td>
                                                <td class="text-right">
                                                    <code>{{ number_format($monthly_unilevel_RO_bonuses, 0, ',', '.') }}</code>
                                                </td>
                                                <td class="text-right">
                                                    <code>{{ number_format($monthly_profit_sharing_13_bonuses, 0, ',', '.') }}</code>
                                                </td>
                                                <td class="text-right">
                                                    <code>{{ number_format($monthly_bonuses, 0, ',', '.') }}</code>
                                                </td>
                                                <td class="text-right">
                                                    <code>{{ number_format($monthly_bonuses > $monthly_admin_fee ? $monthly_admin_fee : 0, 0, ',', '.') }}</code>
                                                </td>
                                                <td class="text-right">
                                                    <code>{{ number_format($monthly_bonuses > 330000 ? ($a->npwp ? ($monthly_bonuses * 5) / 100 : ($monthly_bonuses * 6) / 100) : 0, 0, ',', '.') }}</code>
                                                </td>
                                                <td class="text-right">
                                                    <code>{{ number_format($monthly_total, 0, ',', '.') }}
                                                    </code>
                                                </td>
                                                <td data-search="{{ $status }}">{!! $status_html !!}</td>
                                                <td><code>{{ $monthly_bonus ? ($monthly_bonus->paid_at ? $monthly_bonus->updated_at : '-') : '-' }}</code>
                                                </td>
                                                <td class="text-right">
                                                    @if ($monthly_qualified)
                                                        @if (!$monthly_bonus->paid_at)
                                                            @if ($status != 'Menunggu pembayaran')
                                                                <button class="btn btn-xs btn-rounded btn-success"
                                                                    data-toggle="modal"
                                                                    data-target="#confirm{{ $a->id }}">konfirmasi</button>
                                                            @endif
                                                        @else
                                                            <button class="btn btn-xs btn-rounded btn-danger"
                                                                data-toggle="modal"
                                                                data-target="#cancel{{ $a->id }}">batalkan</button>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="d-none">{{ $a->id }}</td>
                                                <td class="d-none">
                                                    {{ $monthly_qualified && !$monthly_bonus->paid_at ? 1 : 0 }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>#</th>
                                            <th>Member</th>
                                            <th>Rekening</th>
                                            <th class="text-right">Komisi Penjualan (Rp)</th>
                                            <th class="text-right">Bonus Unilevel RO (Rp)</th>
                                            <th class="text-right">Bonus Royalti Profit Sharing 13% (Rp)</th>
                                            <th class="text-right">Total</th>
                                            <th class="text-right">Admin</th>
                                            <th class="text-right">Pajak</th>
                                            <th class="text-right">Ditransfer</th>
                                            <th>Status</th>
                                            <th>Dibayar pada</th>
                                            <th class="text-right">Konfirmasi</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <h4 class="card-title">Keterangan Status</h4>
                            <ul>
                                <li><span class="label label-warning">Harus dibayar</span><span><small> Bonus member harus
                                            dibayarkan bulan ini.</small></span></li>
                                <li><span class="label label-primary">Sudah dibayar</span><span><small> Bonus member sudah
                                            dibayar.</small></span></li>
                                <li><span class="label label-info">Menunggu pembayaran</span><span><small> Bonus member
                                            dibayarkan bulan selanjutnya apabila telah mencapai Rp 50.000.</small></span>
                                </li>
                                <li><span class="label label-danger">Belum qualified</span><span><small> Member belum
                                            memenuhi syarat untuk mendapatkan bonus bulanan.</small></span></li>
                            </ul>
                        </div>
                    </div>
                    @foreach ($users as $a)
                        @if ($a->monthlyQualified($month))
                            @if (!$a->monthlyBonuses($month)->first()->paid_at)
                                <div class="modal inmodal" id="confirm{{ $a->id }}" tabindex="-1"
                                    role="dialog" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content animated fadeInDown">
                                            <div class="modal-header">
                                                <h4 class="modal-title">Konfirmasi</h4>
                                                <button type="button" class="close" data-dismiss="modal"><span
                                                        aria-hidden="true">&times;</span><span
                                                        class="sr-only">Close</span></button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah anda yakin?
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ url('monthly/' . $a->id . '/confirm') }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="month" value="{{ $month }}" />
                                                    <button type="submit" class="btn btn-primary">Konfirmasi</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <form action="{{ url('monthly/' . $a->id . '/cancel') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="updated_at"
                                        value="{{ $a->monthlyBonuses($month)->first()->updated_at }}" />
                                    <div class="modal inmodal" id="cancel{{ $a->id }}" tabindex="-1"
                                        role="dialog" aria-hidden="true">
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
                    @endforeach
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Member Qualified Bonus Generasi</h4>
                            <h6 class="card-subtitle">Member yang memenuhi syarat untuk mendapatkan Bonus Generasi</h6>
                            <div class="table-responsive">
                                <table id="qualified"
                                    class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th data-orderable="false">#</th>
                                            <th>Username</th>
                                            <th>Nama</th>
                                            <th class="text-right">Poin</th>
                                            <th class="text-right">Bonus (Rp)</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Member Qualified Bonus Royalti 13%</h4>
                            <h6 class="card-subtitle">
                                Member yang memenuhi syarat untuk mendapatkan Bonus Royalti 13%.
                                Bonus yang dibagikan sebesar
                                <span class="font-weight-bold" id="royalty-amount">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </span>
                                yang didapatkan dari
                                <span class="font-weight-bold" id="royalty-poin">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </span>
                                PV × 1.000 × 13% ÷
                                <span class="font-weight-bold" id="royalty-qualified">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </span>
                                member
                            </h6>
                            <div class="table-responsive">
                                <table id="royalty"
                                    class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th data-orderable="false">#</th>
                                            <th>Username</th>
                                            <th>Nama</th>
                                            <th class="text-right">Poin</th>
                                            <th class="text-right">Bonus (Rp)</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card table-responsive">
                        <table class="table table-hover table-stripped m-0">
                            <tr>
                                <td>
                                    Komisi Penjualan
                                    <div>
                                        <small class="form-text text-muted">
                                            Total komisi penjualan dari belanja produk
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                        </small>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <code id="cashback">
                                        {{ number_format(Auth::user()->monthlyCashbackBonuses($month)->sum('amount'),0,',','.') }}
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Bonus Unilevel RO <span class="text-danger">{{ !$closing ? '(Potensi)' : '' }}</span>
                                    <div>
                                        <small class="form-text text-muted">
                                            Total bonus unilevel RO
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                        </small>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <code id="potency">
                                        <div class="spinner-grow" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Bonus Bulanan Poin Sharing 13% <span
                                        class="text-danger">{{ !$closing ? '(Potensi)' : '' }}</span>
                                    <div>
                                        <small class="form-text text-muted">
                                            Total bonus bulanan poin sharing 13%
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                        </small>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <code id="poin-sharing-13">
                                        <div class="spinner-grow" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Jumlah</b></td>
                                <td class="text-right font-weight-bold">
                                    <code id="sum">
                                        <div class="spinner-grow" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <td>Biaya Administrasi<div><small class="form-text text-muted">Jumlah bonus &le; Rp
                                            {{ number_format($monthly_admin_fee, 0, ',', '.') }} tidak dikenakan biaya
                                            administrasi</small>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <code id="administrative">
                                        <div class="spinner-grow" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <td>Pajak<div><small class="form-text text-muted">Jumlah bonus ≥ Rp 330.000 dikenakan pajak
                                            sebesar 6% (5% apabila memiliki NPWP)</small>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <code id="tax">
                                        <div class="spinner-grow" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Total</b></td>
                                <td class="text-right font-weight-bold">
                                    <code id="total">
                                        <div class="spinner-grow" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </code>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Komisi Penjualan</h4>
                            <h6 class="card-subtitle">Total komisi penjualan dari belanja produk</h6>
                            <div class="table-responsive">
                                <table id="monthly-cashback"
                                    class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal</th>
                                            <th class="text-right">Bonus (Rp)</th>
                                            <th>Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach (Auth::user()->monthlyCashbackBonuses($month)->get() as $a)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td><code>{{ $a->created_at }}</code></td>
                                                <td class="text-right">
                                                    <code>{{ number_format($a->amount, 0, ',', '.') }}</code>
                                                </td>
                                                <td>{{ $a->description }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Bonus Unilevel RO <span
                                    class="text-danger">{{ !$closing ? '(Potensi)' : '' }}</span></h4>
                            <h6 class="card-subtitle">Total bonus unilevel RO</h6>
                            <div class="table-responsive">
                                <table id="monthly-unilevel-ro"
                                    class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th data-orderable="false">#</th>
                                            <th>Tanggal</th>
                                            <th class="text-right">Bonus (Rp)</th>
                                            <th>Deskripsi</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Bonus Bulanan Poin Sharing 13% <span
                                    class="text-danger">{{ !$closing ? '(Potensi)' : '' }}</span></h4>
                            <h6 class="card-subtitle">Total bonus bulanan poin sharing 13%</h6>
                            <div class="table-responsive">
                                <table id="monthly-13"
                                    class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal</th>
                                            <th class="text-right">Bonus (Rp)</th>
                                            <th>Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach (Auth::user()->monthlyProfitSharing13Bonuses($month)->latest()->get() as $a)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td><code>{{ $a->created_at }}</code></td>
                                                <td class="text-right">
                                                    @if ($a->user->monthlyRoyaltyQualified($month))
                                                        <span class="label label-success">Qualified</span>
                                                    @endif
                                                    <code>{{ number_format($a->amount, 0, ',', '.') }}</code>
                                                </td>
                                                <td>{{ $a->description }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @if (Auth::user()->type == 'admin')
        <form action="{{ url('monthly/confirm') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="month" value="{{ $month }}" />
            <input type="hidden" name="user_ids" />
            <div class="modal inmodal" id="confirm" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content animated fadeInDown">
                        <div class="modal-body">
                            <h3>Konfirmasi Sekaligus</h3>
                            <p>Apakah anda yakin?</p>
                            <div class="text-right">
                                <button type="submit" class="btn btn-success btn-rounded">Konfirmasi</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        @if (!$closing)
            <div class="modal fade closing">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form class="form-material" action="{{ route('monthly-closing.store') }}" method="POST"
                            onsubmit="closing.disabled = true;">
                            @csrf
                            <input type="hidden" name="month" value="{{ $month }}" />
                            <div class="modal-body">
                                <h4>Closing</h4>
                                <p>Apakah Anda yakin?</p>
                                <p class="text-right">
                                    <button name="closing" type="submit"
                                        class="btn btn-danger waves-effect">Closing</button>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment-with-locales.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.37/moment-timezone-with-data.min.js"></script>
    <script src="//cdn.datatables.net/plug-ins/1.11.4/dataRender/datetime.js"></script>
    <script>
        jQuery(document).ready(function() {
            $("#month").on('change', function() {
                document.getElementById("filter").submit();
            });
        });
    </script>
    @if (in_array(Auth::user()->type, ['admin', 'cradmin']))
        <script>
            jQuery(document).ready(function() {
                var monthly = $('#monthly-bonuses').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    order: [
                        [2, "asc"]
                    ],
                    initComplete: function() {
                        $('#table-filter').on('change', function() {
                            monthly.search(this.value).draw();
                        });
                    },
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
                monthly.on('select deselect', function(e, dt, type, indexes) {
                    if (type === 'row') {
                        var payable = monthly.row(indexes).data()[16];
                        if (e.type == 'select' && payable == '0') {
                            monthly.rows('.row-disabled', {
                                selected: true
                            }).deselect();
                        } else if (payable == '1') {
                            var data = monthly.rows({
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
                var qualified = $('#qualified').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    order: [
                        [1, "asc"]
                    ],
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                    },
                    "bProcessing": true,
                    "sAjaxSource": "/qualified?month={{ $month }}",
                    "aoColumns": [{
                            "mDataProp": null
                        },
                        {
                            "mDataProp": "username"
                        },
                        {
                            "mDataProp": "name"
                        },
                        {
                            "mDataProp": "poin",
                            "mRender": function(data) {
                                return '<code>' + data.toLocaleString('id') + '</code>';
                            },
                            "sClass": "text-right",
                        },
                        {
                            "mDataProp": "bonus",
                            "mRender": function(data) {
                                return '<code>' + data.toLocaleString('id') + '</code>';
                            },
                            "sClass": "text-right",
                            "bVisible": {{ $closing ? 'true' : 'false' }},
                        },
                    ]
                });
                qualified.on('order.dt search.dt', function() {
                    qualified.column(0, {
                        search: 'applied',
                        order: 'applied'
                    }).nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1;
                    });
                }).draw();
                var royalty = $('#royalty').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    order: [
                        [1, "asc"]
                    ],
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                    },
                    "bProcessing": true,
                    "sAjaxSource": "/qualified/royalty?month={{ $month }}",
                    "aoColumns": [{
                            "mDataProp": null
                        },
                        {
                            "mDataProp": "username"
                        },
                        {
                            "mDataProp": "name"
                        },
                        {
                            "mDataProp": "poin",
                            "mRender": function(data) {
                                return '<code>' + data.toLocaleString('id') + '</code>';
                            },
                            "sClass": "text-right",
                        },
                        {
                            "mDataProp": "bonus",
                            "mRender": function(data) {
                                return '<code>' + data.toLocaleString('id') + '</code>';
                            },
                            "sClass": "text-right",
                            "bVisible": {{ $closing ? 'true' : 'false' }},
                        },
                    ]
                });
                royalty.on('order.dt search.dt', function() {
                    royalty.column(0, {
                        search: 'applied',
                        order: 'applied'
                    }).nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1;
                    });
                }).draw();
                $.get("/potency/profit-sharing-13?month={{ $month }}", function(data,
                    status) {
                    if (status == 'success') {
                        $('#royalty-amount').html('Rp ' + data.amount.toString().replace(
                            /\B(?=(\d{3})+(?!\d))/g, "."));
                        $('#royalty-poin').html(data.poin.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
                        $('#royalty-qualified').html(data.qualified.toString().replace(/\B(?=(\d{3})+(?!\d))/g,
                            "."));
                    }
                }).fail(function() {
                    $('#royalty-amount').html('Data gagal dimuat');
                    $('#royalty-poin').html('Data gagal dimuat');
                    $('#royalty-qualified').html('Data gagal dimuat');
                });
            });
        </script>
    @else
        <script>
            jQuery(document).ready(function() {
                $.get("/potency/{{ Auth::id() }}?month={{ $month }}", function(
                    data, status) {
                    if (status == 'success') {
                        $('#potency').html(data);
                        $.get("/potency/profit-sharing-13?month={{ $month }}",
                            function(data, status) {
                                if (status == 'success') {
                                    $('#poin-sharing-13').html(data.amount.toString().replace(
                                        /\B(?=(\d{3})+(?!\d))/g, "."));
                                    var cashback = parseFloat($('#cashback').text().replace(/\./g, ""));
                                    var potency = parseFloat($('#potency').text().replace(/\./g, ""));
                                    var poinsharing13 = parseFloat($('#poin-sharing-13').text().replace(
                                        /\./g, ""));
                                    var sum = cashback + potency + poinsharing13;
                                    $('#sum').html(sum.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
                                    var administrative = sum <= 10000 ? 0 : 10000;
                                    $('#administrative').html(administrative.toString().replace(
                                        /\B(?=(\d{3})+(?!\d))/g, "."));
                                    var tax = Math.round(sum >= 330000 ? ('{{ Auth::user()->npwp }}' !=
                                        '' ? (sum * 5 / 100) : (sum * 6 / 100)) : 0);
                                    $('#tax').html(tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
                                    var total = sum - administrative - tax;
                                    $('#total').html(total.toString().replace(/\B(?=(\d{3})+(?!\d))/g,
                                        "."));
                                }
                            }).fail(function() {
                            $('#poin-sharing-13').html('Data gagal dimuat');
                            $('#sum').html('Data gagal dimuat');
                            $('#administrative').html('Data gagal dimuat');
                            $('#tax').html('Data gagal dimuat');
                            $('#total').html('Data gagal dimuat');
                        });
                    } else {
                        $('#potency').html('Data gagal dimuat');
                    }
                }).fail(function() {
                    $('#potency').html('Data gagal dimuat');
                });
                var potency = $('#monthly-unilevel-ro').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    order: [
                        [1, "desc"]
                    ],
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                    },
                    "bProcessing": true,
                    "sAjaxSource": "/potency/{{ Auth::id() }}/list?month={{ $month }}",
                    "aoColumns": [{
                            "mDataProp": null
                        },
                        {
                            "mDataProp": "created_at",
                            "mRender": function(data) {
                                return '<code>' + (moment(data).isValid() ? moment(data).tz(
                                    'Asia/Jakarta').format('YYYY-MM-DD HH:mm:ss') : '') + '</code>';
                            },
                        },
                        {
                            "mDataProp": "amount",
                            "mRender": function(data) {
                                return '<code>' + data.toLocaleString('id') + '</code>';
                            },
                            "sClass": "text-right",
                        },
                        {
                            "mDataProp": "description"
                        },
                    ]
                });
                potency.on('order.dt search.dt', function() {
                    potency.column(0, {
                        search: 'applied',
                        order: 'applied'
                    }).nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1;
                    });
                }).draw();
                $('#monthly-13').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    order: [
                        [1, "desc"]
                    ],
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                    },
                });
                $('#monthly-cashback').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    order: [
                        [1, "desc"]
                    ],
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                    },
                });
            });
        </script>
    @endif
@endsection
