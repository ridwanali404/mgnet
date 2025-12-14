@extends('layout.app')
@section('title', 'Bonus Bulanan')
@section('style')
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.3.3/css/select.dataTables.min.css">
    <style>
        .dt-bootstrap4 {
            padding: 0 !important;
        }

        .spinner-border.spinner-border-sm {
            margin-bottom: 2px;
        }
        
        .scroll-to-omset {
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .scroll-to-omset:hover {
            opacity: 0.7;
            transform: translateY(2px);
        }
        
        .scroll-to-omset:hover .mdi-arrow-down {
            color: #1976d2 !important;
        }
        
        .scroll-to-omset:hover small {
            color: #1976d2 !important;
        }
        
        /* Override CSS untuk checkbox header check all - Material Design */
        #check-all-monthly.check-all-header {
            position: relative !important;
            left: 0 !important;
            opacity: 1 !important;
            width: 18px !important;
            height: 18px !important;
            cursor: pointer !important;
            margin: 0 !important;
            z-index: 1 !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
        }
        
        /* Material Design styling untuk checkbox header */
        #check-all-monthly.check-all-header:before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 18px !important;
            height: 18px !important;
            border: 2px solid #5a5a5a !important;
            border-radius: 1px !important;
            background-color: #fff !important;
            z-index: 0 !important;
        }
        
        #check-all-monthly.check-all-header:checked:before {
            top: -4px !important;
            left: -5px !important;
            width: 12px !important;
            height: 22px !important;
            border-top: 2px solid transparent !important;
            border-left: 2px solid transparent !important;
            border-right: 2px solid #26a69a !important;
            border-bottom: 2px solid #26a69a !important;
            transform: rotate(40deg) !important;
            transform-origin: 100% 100% !important;
        }
        
        /* Material Design checkbox untuk setiap record di DataTables */
        td.select-checkbox {
            position: relative !important;
        }
        
        td.select-checkbox:before {
            content: '' !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            width: 18px !important;
            height: 18px !important;
            border: 2px solid #5a5a5a !important;
            border-radius: 1px !important;
            z-index: 0 !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }
        
        tr.selected td.select-checkbox:before {
            top: calc(50% - 4px) !important;
            left: calc(50% - 5px) !important;
            width: 12px !important;
            height: 22px !important;
            border-top: 2px solid transparent !important;
            border-left: 2px solid transparent !important;
            border-right: 2px solid #26a69a !important;
            border-bottom: 2px solid #26a69a !important;
            transform: translate(-50%, -50%) rotate(40deg) !important;
            transform-origin: 100% 100% !important;
        }
        
        /* Hide default DataTables checkbox icon and text */
        td.select-checkbox:after {
            display: none !important;
        }
        
        td.select-checkbox {
            cursor: pointer !important;
            text-align: center !important;
        }
        
        /* Hide any content inside select-checkbox cell */
        td.select-checkbox > * {
            display: none !important;
        }
        
        /* Ensure proper sizing */
        th:first-child,
        td.select-checkbox {
            width: 30px !important;
            min-width: 30px !important;
            max-width: 30px !important;
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
                        @else
                            <form action="{{ route('monthly-closing.cancel') }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan closing untuk bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}? Tindakan ini akan: menghapus bonus Profit Sharing, mengembalikan masa aktif user, dan menghapus record closing.');">
                                @csrf
                                <input type="hidden" name="month" value="{{ $month }}" />
                                <button type="submit" class="btn waves-effect waves-light btn-warning pull-right">
                                    Batal Closing {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                </button>
                            </form>&nbsp;
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
                        @if ($monthly_bonuses->first() && $monthly_bonuses->first()->paid_at)
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
            @endif
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            @php
                                $startDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                                $endDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();
                                $latestQualification = Auth::user()
                                    ->powerPlusQualifications()
                                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                                    ->orderBy('date', 'desc')
                                    ->first();
                                
                                // Pastikan leg_omzets selalu array
                                $legOmzets = [];
                                if ($latestQualification && $latestQualification->leg_omzets) {
                                    $legOmzetsData = $latestQualification->leg_omzets;
                                    // Jika masih string (JSON), decode dulu
                                    if (is_string($legOmzetsData)) {
                                        $legOmzets = json_decode($legOmzetsData, true) ?: [];
                                    } elseif (is_array($legOmzetsData)) {
                                        $legOmzets = $legOmzetsData;
                                    }
                                }
                                
                                $hasData = !empty($legOmzets) && is_array($legOmzets);
                                $totalOmzet = $hasData ? array_sum($legOmzets) : 0;
                            @endphp
                            <div class="d-flex flex-row">
                                <div class="round round-lg align-self-center round-danger"><i class="mdi mdi-chart-line"></i>
                                </div>
                                <div class="m-l-10 align-self-center">
                                    <h3 class="m-b-0 font-light">
                                        {{ number_format($totalOmzet, 0, ',', '.') }}&nbsp;
                                    </h3>
                                    <h5 class="text-muted m-b-0">
                                        Omset Grup Powerplus
                                        @if(!$hasData)
                                            <i class="mdi mdi-information-outline" 
                                               data-toggle="tooltip" 
                                               data-placement="top" 
                                               data-html="true"
                                               title="Belum ada data omset grup untuk bulan ini"></i>
                                        @endif
                                    </h5>
                                    <a href="#omset-grup-powerplus" class="scroll-to-omset" style="text-decoration: none; color: inherit; cursor: pointer;">
                                        <i class="mdi mdi-arrow-down text-muted" style="font-size: 14px;"></i>
                                        <small class="text-muted">Lihat detail di bawah</small>
                                    </a>
                                </div>
                            </div>
                            @if($hasData)
                                <div class="mt-2">
                                    @foreach($legOmzets as $legName => $omzet)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">{{ $legName }}:</small>
                                            <strong>{{ number_format($omzet, 0, ',', '.') }} Poin</strong>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
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
                                    <h5 class="text-muted m-b-0">
                                        Poin Value 
                                        <i class="mdi mdi-information-outline" 
                                           data-toggle="tooltip" 
                                           data-placement="top" 
                                           data-html="true"
                                           title="Setiap produk memiliki PV satuan retail. PV berfungsi sebagai kumpulan poin. Ketika mencapai 170 PV, member dianggap telah melakukan RO (Repeat Order) dan bonus akan naik ke upline seperti bonus paket Join Gold, namun tanpa bonus sponsor."></i>
                                    </h5>
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
                                            Bonus Global Profit Sharing dan Power Plus akan diakumulasi setelah closing
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
                                        {{-- <option>Belum qualified</option> --}}
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="monthly-bonuses"
                                    class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="check-all-monthly" class="check-all-header" title="Pilih Semua">
                                            </th>
                                            <th data-orderable=false>#</th>
                                            <th>Join</th>
                                            <th>Member</th>
                                            <th>Rekening</th>
                                            <th class="text-right">Komisi Penjualan (Rp)</th>
                                            <th class="text-right">Global Profit Sharing (Rp)</th>
                                            <th class="text-right">Bonus Power Plus (Rp)</th>
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
                                                
                                                // Ambil bonus Global Profit Sharing (GPS)
                                                // Cek apakah ada bonus Global Profit Sharing (setelah closing)
                                                $monthly_gps_bonuses = $a->bonuses()
                                                    ->whereYear('created_at', date('Y', strtotime($month)))
                                                    ->whereMonth('created_at', date('m', strtotime($month)))
                                                    ->where('type', 'Bonus Global Profit Sharing')
                                                    ->sum('amount');
                                                
                                                // Jika belum ada bonus GPS (belum closing), ambil dari GPS daily untuk bulan tersebut
                                                if ($monthly_gps_bonuses == 0) {
                                                    $startDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                                                    $endDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();
                                                    
                                                    // Hitung total GPS daily untuk bulan tersebut
                                                    $gpsDailyTotal = $a->globalProfitSharingDailies()
                                                        ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                                                        ->sum('amount');
                                                    
                                                    // Ambil wallet_cashback dari GPS saving
                                                    // Tapi untuk display, gunakan min antara wallet_cashback dan gpsDailyTotal
                                                    // Karena wallet_cashback bisa berisi akumulasi dari bulan lain
                                                    $gpsSaving = $a->globalProfitSharingSavings()->first();
                                                    if ($gpsSaving && $gpsSaving->wallet_cashback > 0) {
                                                        // Pastikan tidak melebihi GPS daily untuk bulan tersebut
                                                        $monthly_gps_bonuses = min($gpsDailyTotal, $gpsSaving->wallet_cashback);
                                                    } else {
                                                        $monthly_gps_bonuses = $gpsDailyTotal;
                                                    }
                                                }
                                                
                                                // Bonus Profit Sharing lama sudah dihapus, tidak digunakan lagi
                                                // Hanya menggunakan Bonus Global Profit Sharing (GPS)
                                                
                                                $monthly_power_plus_bonuses = $a->monthlyPowerPlusBonuses($month)->sum('amount');
                                                $monthly_bonus = $a->monthlyBonuses($month)->first();
                                                // monthlyBonuses sudah tidak include Bonus Profit Sharing lama, hanya GPS
                                                $monthly_bonuses = $a->monthlyBonuses($month)->sum('amount');
                                                $monthly_qualified = $a->monthlyQualified($month);
                                                $monthly_total = $monthly_bonuses - ($monthly_bonuses > $monthly_admin_fee ? $monthly_admin_fee : 0) - ($monthly_bonuses > 330000 ? ($a->npwp ? ($monthly_bonuses * 5) / 100 : ($monthly_bonuses * 6) / 100) : 0);
                                                if (!$monthly_qualified) {
                                                    $status_html = '<span class="label label-danger">Belum qualified</span>';
                                                    $status = 'Belum qualified';
                                                } else {
                                                    if ($monthly_bonus && $monthly_bonus->paid_at) {
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
                                                    <code>{{ number_format($monthly_gps_bonuses, 0, ',', '.') }}</code>
                                                </td>
                                                <td class="text-right">
                                                    <code>{{ number_format($monthly_power_plus_bonuses, 0, ',', '.') }}</code>
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
                                                        @if (!$monthly_bonus || !$monthly_bonus->paid_at)
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
                                                    {{ $monthly_qualified && (!$monthly_bonus || !$monthly_bonus->paid_at) ? 1 : 0 }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
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
                            @php
                                $userMonthlyBonus = $a->monthlyBonuses($month)->first();
                            @endphp
                            @if (!$userMonthlyBonus || !$userMonthlyBonus->paid_at)
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
                    @php
                        // Hitung total bonus bulanan
                        $totalBonusHarusDibayar = 0;
                        $totalBonusSudahDibayar = 0;
                        
                        foreach ($users as $a) {
                            $monthly_bonuses = $a->monthlyBonuses($month)->sum('amount');
                            $monthly_qualified = $a->monthlyQualified($month);
                            $monthly_bonus = $a->monthlyBonuses($month)->first();
                            $monthly_total = $monthly_bonuses - ($monthly_bonuses > $monthly_admin_fee ? $monthly_admin_fee : 0) - ($monthly_bonuses > 330000 ? ($a->npwp ? ($monthly_bonuses * 5) / 100 : ($monthly_bonuses * 6) / 100) : 0);
                            
                            if ($monthly_qualified) {
                                if ($monthly_bonus && $monthly_bonus->paid_at) {
                                    // Sudah dibayar
                                    $totalBonusSudahDibayar += $monthly_total;
                                } elseif ($monthly_total >= 50000) {
                                    // Harus dibayar
                                    $totalBonusHarusDibayar += $monthly_total;
                                }
                            }
                        }
                    @endphp
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Total Bonus Bulan ini</h4>
                            <h6 class="card-subtitle">Ringkasan total bonus bulanan untuk {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <h5 class="card-title text-white">Harus Dibayar</h5>
                                            <h2 class="text-white mb-0">
                                                <code class="text-white" style="font-size: 1.5em;">
                                                    Rp {{ number_format($totalBonusHarusDibayar, 0, ',', '.') }}
                                                </code>
                                            </h2>
                                            <small>Total bonus yang harus dibayarkan bulan ini</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <h5 class="card-title text-white">Sudah Dibayar</h5>
                                            <h2 class="text-white mb-0">
                                                <code class="text-white" style="font-size: 1.5em;">
                                                    Rp {{ number_format($totalBonusSudahDibayar, 0, ',', '.') }}
                                                </code>
                                            </h2>
                                            <small>Total bonus yang sudah dibayarkan</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Bonus Power Plus <span
                                    class="text-danger">{{ !$closing ? '(Potensi)' : '' }}</span></h4>
                            <h6 class="card-subtitle">Bonus Power Plus untuk yang Qualified</h6>
                            <div class="table-responsive">
                                <table id="admin-monthly-power-plus"
                                    class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Username</th>
                                            <th>Nama</th>
                                            <th class="text-right">Bonus (Rp)</th>
                                            <th>Deskripsi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $counter = 1;
                                        @endphp
                                        @foreach ($users as $user)
                                            @foreach ($user->monthlyPowerPlusBonuses($month)->latest()->get() as $bonus)
                                                <tr>
                                                    <td>{{ $counter++ }}</td>
                                                    <td>
                                                        <a href="{{ url('user/' . $user->id . '/profile') }}">
                                                            {{ $user->username }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $user->name }}</td>
                                                    <td class="text-right">
                                                        <code>{{ number_format($bonus->amount, 0, ',', '.') }}</code>
                                                    </td>
                                                    <td>{{ $bonus->description }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Historis Harian Global Profit Sharing <span
                                    class="text-danger">{{ !$closing ? '(Potensi)' : '' }}</span></h4>
                            <h6 class="card-subtitle">Riwayat Global Profit Sharing per tanggal untuk bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}</h6>
                            <div class="table-responsive">
                                <table id="admin-monthly-global-profit-sharing-daily"
                                    class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal</th>
                                            <th class="text-right">Total GPS Dibagikan (Rp)</th>
                                            <th class="text-center">Jumlah Member</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $counter = 1;
                                            $startDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                                            $endDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();
                                            
                                            // Ambil data GPS daily per tanggal
                                            $gpsDailyByDate = \App\Models\GlobalProfitSharingDaily::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                                                ->selectRaw('date, SUM(amount) as total_amount, COUNT(DISTINCT user_id) as member_count')
                                                ->groupBy('date')
                                                ->orderBy('date', 'desc')
                                                ->get();
                                        @endphp
                                        @foreach ($gpsDailyByDate as $dateData)
                                            <tr data-date="{{ $dateData->date }}">
                                                    <td>{{ $counter++ }}</td>
                                                    <td>
                                                    <code>{{ \Carbon\Carbon::parse($dateData->date)->translatedFormat('d F Y') }}</code>
                                                    </td>
                                                    <td class="text-right">
                                                    <code>{{ number_format($dateData->total_amount, 0, ',', '.') }}</code>
                                                </td>
                                                <td class="text-center">
                                                    <code>{{ $dateData->member_count }}</code>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-info btn-view-detail" 
                                                            data-date="{{ $dateData->date }}"
                                                            data-toggle="modal" 
                                                            data-target="#gps-detail-modal">
                                                        <i class="mdi mdi-eye"></i> Lihat Rincian
                                                    </button>
                                                    </td>
                                                </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Omset Grup Powerplus per Member</h4>
                            <h6 class="card-subtitle">Lihat omset grup powerplus untuk member tertentu</h6>
                            <div class="form-group mt-3">
                                <label>Pilih Member</label>
                                <select id="powerplus-member-select" class="form-control" style="width: 100%;">
                                    <option value="">-- Pilih Member --</option>
                                </select>
                            </div>
                            <div id="powerplus-member-omzet" style="display: none;">
                                <div class="card table-responsive mt-3">
                                    <table class="table table-hover table-stripped m-0">
                                        <thead>
                                            <tr style="line-height: 1.3;">
                                                <th class="text-center">Leg<br><small class="text-muted">Nama grup</small></th>
                                                <th class="text-center">Omset (Poin)<br><small class="text-muted">Total omset grup</small></th>
                                            </tr>
                                        </thead>
                                        <tbody id="powerplus-leg-omzet-body">
                                        </tbody>
                                    </table>
                                </div>
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
                                    Bonus Power Plus
                                    <div>
                                        <small class="form-text text-muted">
                                            Total bonus Power Plus
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                        </small>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <code id="power-plus">
                                        {{ number_format(Auth::user()->monthlyPowerPlusBonuses($month)->sum('amount'),0,',','.') }}
                                    </code>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Bonus Global Profit Sharing
                                    <div>
                                        <small class="form-text text-muted">
                                            Total bonus Global Profit Sharing
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                                        </small>
                                    </div>
                                </td>
                                <td class="text-right">
                                    @php
                                        // Cek apakah ada bonus Global Profit Sharing (setelah closing)
                                        $gpsBonusTotal = Auth::user()->bonuses()
                                            ->whereYear('created_at', date('Y', strtotime($month)))
                                            ->whereMonth('created_at', date('m', strtotime($month)))
                                            ->where('type', 'Bonus Global Profit Sharing')
                                            ->sum('amount');
                                        
                                        // Jika belum ada bonus, ambil dari GPS saving wallet_cashback
                                        if ($gpsBonusTotal == 0) {
                                            $gpsSaving = Auth::user()->globalProfitSharingSavings()->first();
                                            $gpsBonusTotal = $gpsSaving ? $gpsSaving->wallet_cashback : 0;
                                        }
                                    @endphp
                                    <code id="profit-sharing">
                                        {{ number_format($gpsBonusTotal,0,',','.') }}
                                    </code>
                                </td>
                            </tr>
                            {{-- Bonus Unilevel RO dan Bonus Bulanan Poin Sharing 13% sudah dihilangkan --}}
                            {{-- 
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
                            --}}
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
                    {{-- Bonus Unilevel RO dan Bonus Bulanan Poin Sharing 13% sudah dihilangkan --}}
                    {{-- 
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
                    --}}
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Bonus Total Global Profit Sharing <span
                                    class="text-danger">{{ !$closing ? '(Potensi)' : '' }}</span></h4>
                            <h6 class="card-subtitle">Total bonus profit sharing untuk Platinum</h6>
                            @php
                                // Ambil bonus Global Profit Sharing yang sudah dibuat (setelah closing)
                                $gpsBonuses = Auth::user()->bonuses()
                                    ->whereYear('created_at', date('Y', strtotime($month)))
                                    ->whereMonth('created_at', date('m', strtotime($month)))
                                    ->where('type', 'Bonus Global Profit Sharing')
                                    ->latest()
                                    ->get();
                                
                                // Jika belum ada bonus (belum closing), ambil dari GPS daily untuk bulan tersebut
                                if ($gpsBonuses->count() == 0) {
                                    $startDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                                    $endDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();
                                    $gpsDaily = Auth::user()->globalProfitSharingDailies()
                                        ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                                        ->orderBy('date', 'desc')
                                        ->get();
                                    
                                    // Jika ada GPS saving, tampilkan wallet cashback sebagai potensi
                                    $gpsSaving = Auth::user()->globalProfitSharingSavings()->first();
                                    $totalGpsPotential = $gpsSaving ? $gpsSaving->wallet_cashback : $gpsDaily->sum('amount');
                                } else {
                                    $gpsDaily = collect();
                                    $totalGpsPotential = 0;
                                }
                            @endphp
                            <div class="table-responsive">
                                <table id="monthly-profit-sharing"
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
                                        @if($gpsBonuses->count() > 0)
                                            {{-- Tampilkan bonus yang sudah dibuat (setelah closing) --}}
                                            @foreach ($gpsBonuses as $a)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                    <td><code>{{ $a->created_at->format('Y-m-d') }}</code></td>
                                                <td class="text-right">
                                                    <code>{{ number_format($a->amount, 0, ',', '.') }}</code>
                                                </td>
                                                <td>{{ $a->description }}</td>
                                            </tr>
                                        @endforeach
                                        @elseif($gpsDaily->count() > 0)
                                            {{-- Tampilkan GPS daily sebagai potensi (belum closing) --}}
                                            @foreach ($gpsDaily as $daily)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                    <td><code>{{ \Carbon\Carbon::parse($daily->date)->format('Y-m-d') }}</code></td>
                                                <td class="text-right">
                                                    <code>{{ number_format($daily->amount, 0, ',', '.') }}</code>
                                                </td>
                                                    <td>Global Profit Sharing harian</td>
                                            </tr>
                                        @endforeach
                                        @endif
                                    </tbody>
                                    @if($totalGpsPotential > 0 && $gpsBonuses->count() == 0)
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-right">Total Potensi:</th>
                                            <th class="text-right">
                                                <code>Rp {{ number_format($totalGpsPotential, 0, ',', '.') }}</code>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Bonus Power Plus <span
                                    class="text-danger">{{ !$closing ? '(Potensi)' : '' }}</span></h4>
                            <h6 class="card-subtitle">Bonus Power Plus untuk yang Qualified</h6>
                            <div class="table-responsive">
                                <table id="monthly-power-plus"
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
                                        @foreach (Auth::user()->monthlyPowerPlusBonuses($month)->latest()->get() as $a)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td><code>{{ $a->created_at->format('Y-m-d') }}</code></td>
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
                    <div class="card" id="omset-grup-powerplus">
                        <div class="card-body">
                            <h4 class="card-title">Omset Grup Powerplus</h4>
                            <h6 class="card-subtitle">Omset grup powerplus untuk bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}</h6>
                            @php
                                $startDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                                $endDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();
                                $powerPlusQualifications = Auth::user()
                                    ->powerPlusQualifications()
                                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                                    ->orderBy('date', 'desc')
                                    ->get();
                                $latestQualification = $powerPlusQualifications->first();
                                
                                // Ambil semua leg berdasarkan jumlah upline user (bukan dari qualification)
                                // Semua leg dihitung untuk Power Plus (tidak ada Leg Kiri yang dikecualikan)
                                $allUplines = Auth::user()->uplines()
                                    ->whereHas('premiumUserPin')
                                    ->orderBy('created_at', 'asc')
                                    ->get();
                                
                                // Buat array leg lengkap berdasarkan jumlah upline dan mapping username
                                $allLegs = [];
                                $legUsernames = []; // Mapping leg name ke username
                                foreach ($allUplines as $index => $upline) {
                                    $legName = 'Leg ' . ($index + 1);
                                    $allLegs[] = $legName;
                                    $legUsernames[$legName] = $upline->username;
                                }
                                
                                // Ambil leg_omzets dari latest qualification atau hitung langsung
                                $legOmzets = [];
                                if ($latestQualification && $latestQualification->leg_omzets) {
                                    $legOmzetsData = $latestQualification->leg_omzets;
                                    // Jika masih string (JSON), decode dulu
                                    if (is_string($legOmzetsData)) {
                                        $legOmzets = json_decode($legOmzetsData, true) ?: [];
                                    } elseif (is_array($legOmzetsData)) {
                                        $legOmzets = $legOmzetsData;
                                    }
                                } else {
                                    // Hanya hitung jika ada leg
                                    if (!empty($allLegs)) {
                                        $legOmzets = \App\Traits\Helper::calculateAllLegOmzetMonthly(Auth::user(), $month);
                                    }
                                }
                                
                                // Pastikan semua leg ada di legOmzets (isi dengan 0 jika belum ada)
                                // Hanya jika ada leg yang ditemukan
                                if (!empty($allLegs)) {
                                    foreach ($allLegs as $legName) {
                                        if (!isset($legOmzets[$legName])) {
                                            $legOmzets[$legName] = 0;
                                        }
                                    }
                                    
                                    // Sort legOmzets berdasarkan nomor leg
                                    uksort($legOmzets, function($a, $b) {
                                        $numA = intval(str_replace('Leg ', '', $a));
                                        $numB = intval(str_replace('Leg ', '', $b));
                                        return $numA - $numB;
                                    });
                                }
                            @endphp
                            {{-- Table Omset Grup Powerplus --}}
                            <div class="card table-responsive mt-3">
                                <table class="table table-hover table-stripped m-0">
                                    <thead>
                                        <tr style="line-height: 1.3;">
                                            <th class="text-center">Leg<br><small class="text-muted">Nama grup</small></th>
                                            <th class="text-center">Omset (Poin)<br><small class="text-muted">Total omset grup</small></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($legOmzets))
                                            @foreach($legOmzets as $legName => $omzet)
                                                <tr>
                                                    <td class="text-center">
                                                        <strong>{{ $legName }}</strong>
                                                        @if(isset($legUsernames[$legName]))
                                                            <br><small class="text-muted">({{ $legUsernames[$legName] }})</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($omzet > 0)
                                                            <a href="javascript:void(0)" 
                                                               class="leg-omzet-detail" 
                                                               data-leg="{{ $legName }}"
                                                               data-month="{{ $month }}"
                                                               data-user-id="{{ Auth::user()->id }}"
                                                               style="text-decoration: none; color: inherit; cursor: pointer;">
                                                                <code class="font-weight-bold" style="font-size: 1.1em; color: #e91e63;">{{ number_format($omzet, 0, ',', '.') }}</code>
                                                            </a>
                                                        @else
                                                            <code class="font-weight-bold" style="font-size: 1.1em;">{{ number_format($omzet, 0, ',', '.') }}</code>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="2" class="text-center">Tidak ada data omset grup untuk bulan ini</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Keterangan Bonus Bulanan</h4>
                            <div class="mt-3">
                                <p class="mb-2">Statement Bonus Bulanan terdiri dari:</p>
                                <ol>
                                    <li><strong>Komisi Penjualan</strong> - Komisi yang diperoleh dari selisih harga jual dan beli produk</li>
                                    <li><strong>Bonus Total Global Profit Sharing</strong> - Bonus profit sharing untuk member Platinum, dilengkapi dengan historis harian detail</li>
                                    <li><strong>Bonus Power Plus</strong> - Bonus untuk member yang telah memenuhi syarat kualifikasi</li>
                                    <li><strong>Bonus Cash Reward Trip</strong> - Bonus trip dapat dilihat di menu Reward tersendiri (seperti menu Automaintain). Bonus ini dihitung harian untuk member yang qualified, dan dapat diajukan klaim ketika mencapai nominal tertentu yang ditentukan oleh Admin</li>
                                </ol>
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
    <script src="https://cdn.datatables.net/select/1.3.3/js/dataTables.select.min.js"></script>
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
    <script src="https://cdn.datatables.net/plug-ins/1.11.4/dataRender/datetime.js"></script>
    <script>
        jQuery(document).ready(function() {
            $("#month").on('change', function() {
                document.getElementById("filter").submit();
            });
            
            // Smooth scroll ke bagian Omset Grup Powerplus
            $('.scroll-to-omset').on('click', function(e) {
                e.preventDefault();
                var target = $('#omset-grup-powerplus');
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100 // Offset 100px dari atas
                    }, 800); // Durasi 800ms untuk smooth scroll
                }
            });
        });

        // Handle click pada angka omset leg
        $(document).on('click', '.leg-omzet-detail', function() {
            var legName = $(this).data('leg');
            var month = $(this).data('month');
            var userId = $(this).data('user-id') || {{ Auth::user()->id }};
            
            // Tampilkan loading
            $('#leg-omzet-modal .modal-body').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat data...</div>');
            $('#leg-omzet-modal').modal('show');
            
            // Load data breakdown
            $.get('/api/powerplus-leg-omzet-breakdown', {
                user_id: userId,
                leg_number: legName,
                month: month,
                
            }, function(data) {
                if (data.success) {
                    var html = '<h5>Detail Omset ' + data.leg_name + ' (' + data.leg_username + ')</h5>';
                    html += '<p class="text-muted">Kolom poin adalah akumulasi poin dari semua downline</strong></p>';
                    html += '<hr>';
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-sm table-hover">';
                    html += '<thead><tr><th>#</th><th>Username</th><th>Nama</th><th class="text-right">Poin</th></tr></thead>';
                    html += '<tbody>';
                    
                    if (data.breakdown && data.breakdown.length > 0) {
                        $.each(data.breakdown, function(index, item) {
                            html += '<tr>';
                            html += '<td>' + (index + 1) + '</td>';
                            html += '<td><code>' + item.username + '</code></td>';
                            html += '<td>' + item.name + '</td>';
                            html += '<td class="text-right"><strong>' + parseInt(item.poin).toLocaleString('id-ID') + '</strong></td>';
                            html += '</tr>';
                        });
                    } else {
                        html += '<tr><td colspan="4" class="text-center text-muted">Tidak ada data</td></tr>';
                    }
                    
                    html += '</tbody></table></div>';
                    $('#leg-omzet-modal .modal-body').html(html);
                } else {
                    $('#leg-omzet-modal .modal-body').html('<div class="alert alert-danger">' + (data.message || 'Gagal memuat data') + '</div>');
                }
            }).fail(function(xhr) {
                var errorMessage = 'Gagal memuat data. Silakan coba lagi.';
                if (xhr.status === 403) {
                    errorMessage = 'Akses ditolak. Hanya admin yang dapat melihat detail breakdown omset leg.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $('#leg-omzet-modal .modal-body').html('<div class="alert alert-danger">' + errorMessage + '</div>');
            });
        });
    </script>
    @if (in_array(Auth::user()->type, ['admin', 'cradmin']))
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script>
            jQuery(document).ready(function() {
                // Initialize Select2 for Powerplus member select
                $("#powerplus-member-select").select2({
                    placeholder: "Cari member...",
                    allowClear: true,
                    ajax: {
                        url: '/filter-user',
                        data: function(params) {
                            return {
                                search: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.data,
                                pagination: {
                                    more: (data.current_page * data.per_page) < data.total
                                }
                            };
                        },
                        cache: true,
                    }
                }).on("select2:select", function(e) {
                    var userId = e.params.data.id;
                    var month = "{{ $month }}";
                    
                    // Load omset grup untuk member yang dipilih
                    $.get('/api/powerplus-leg-omzet', {
                        user_id: userId,
                        month: month
                    }, function(data) {
                        if (data.success && data.leg_omzets) {
                            var html = '';
                            // Pastikan semua leg ditampilkan, termasuk yang nilainya 0
                            var legNames = Object.keys(data.leg_omzets).sort(function(a, b) {
                                // Sort: Leg 1, Leg 2, Leg 3, dst
                                var numA = parseInt(a.replace('Leg ', '')) || 0;
                                var numB = parseInt(b.replace('Leg ', '')) || 0;
                                return numA - numB;
                            });
                            
                            if (legNames.length > 0) {
                                $.each(legNames, function(index, legName) {
                                    var omzet = data.leg_omzets[legName] || 0;
                                    var username = (data.leg_usernames && data.leg_usernames[legName]) ? data.leg_usernames[legName] : '';
                                    html += '<tr>';
                                    html += '<td class="text-center">';
                                    html += '<strong>' + legName + '</strong>';
                                    if (username) {
                                        html += '<br><small class="text-muted">(' + username + ')</small>';
                                    }
                                    html += '</td>';
                                    html += '<td class="text-center">';
                                    if (omzet > 0) {
                                        html += '<a href="javascript:void(0)" class="leg-omzet-detail" data-leg="' + legName + '" data-month="' + month + '" data-user-id="' + userId + '" style="text-decoration: none; color: inherit; cursor: pointer;">';
                                        html += '<code class="font-weight-bold" style="font-size: 1.1em; color: #e91e63;">' + parseInt(omzet).toLocaleString('id-ID') + '</code>';
                                        html += '</a>';
                                    } else {
                                        html += '<code class="font-weight-bold" style="font-size: 1.1em;">' + parseInt(omzet).toLocaleString('id-ID') + '</code>';
                                    }
                                    html += '</td>';
                                    html += '</tr>';
                                });
                            } else {
                                html = '<tr><td colspan="2" class="text-center">Tidak ada data omset grup untuk bulan ini</td></tr>';
                            }
                            $('#powerplus-leg-omzet-body').html(html);
                            $('#powerplus-member-omzet').show();
                        } else {
                            $('#powerplus-leg-omzet-body').html('<tr><td colspan="2" class="text-center">Tidak ada data omset grup untuk bulan ini</td></tr>');
                            $('#powerplus-member-omzet').show();
                        }
                    }).fail(function() {
                        $('#powerplus-leg-omzet-body').html('<tr><td colspan="2" class="text-center text-danger">Gagal memuat data</td></tr>');
                        $('#powerplus-member-omzet').show();
                    });
                }).on("select2:clear", function() {
                    $('#powerplus-member-omzet').hide();
                    $('#powerplus-leg-omzet-body').html('');
                });
                
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
                // Check all functionality
                $('#check-all-monthly').on('click', function() {
                    if ($(this).is(':checked')) {
                        // Select all rows that are not disabled
                        monthly.rows().every(function() {
                            var rowData = this.data();
                            if (rowData[16] == '1') { // Only select payable rows
                                this.select();
                            }
                        });
                    } else {
                        // Deselect all rows
                        monthly.rows().deselect();
                    }
                });
                
                // Function to update check all checkbox state
                function updateCheckAllState() {
                    var selectedRows = monthly.rows({selected: true}).count();
                    var payableRows = 0;
                    
                    monthly.rows().every(function() {
                        var rowData = this.data();
                        if (rowData[16] == '1') {
                            payableRows++;
                        }
                    });
                    
                    if (selectedRows === payableRows && payableRows > 0) {
                        $('#check-all-monthly').prop('checked', true);
                    } else {
                        $('#check-all-monthly').prop('checked', false);
                    }
                }
                
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
                            
                            // Update check all checkbox state
                            updateCheckAllState();
                        }
                    } else if (type === 'page') {
                        // Update check all state when page changes
                        updateCheckAllState();
                    }
                });
                $('#admin-monthly-power-plus').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    order: [
                        [1, "asc"]
                    ],
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                    },
                });
                // Format function untuk child rows
                function format (gpsDailyData) {
                    var table = '<table class="table table-sm table-bordered" style="margin-left: 50px; width: calc(100% - 50px);">' +
                        '<thead>' +
                        '<tr>' +
                        '<th>Tanggal</th>' +
                        '<th class="text-right">Global Profit Sharing (Rp)</th>' +
                        '</tr>' +
                        '</thead>' +
                        '<tbody>';
                    
                    if (gpsDailyData && gpsDailyData.length > 0) {
                        gpsDailyData.forEach(function(daily) {
                            table += '<tr>' +
                                '<td><code>' + daily.date + '</code></td>' +
                                '<td class="text-right"><code>' + daily.amount + '</code></td>' +
                                '</tr>';
                        });
                    } else {
                        table += '<tr><td colspan="2" class="text-center">Tidak ada data</td></tr>';
                    }
                    
                    table += '</tbody></table>';
                    return table;
                }
                
                var table = $('#admin-monthly-global-profit-sharing-daily').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    order: [
                        [1, "desc"]
                    ],
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                    },
                });
                
                // Handle click pada tombol "Lihat Rincian"
                $('#admin-monthly-global-profit-sharing-daily tbody').on('click', 'button.btn-view-detail', function () {
                    var date = $(this).data('date');
                    var modal = $('#gps-detail-modal');
                    var modalBody = modal.find('.modal-body');
                    
                    // Tampilkan loading
                    modalBody.html('<div class="text-center"><i class="mdi mdi-loading mdi-spin" style="font-size: 2em;"></i><p>Memuat data...</p></div>');
                    
                    // Load data via AJAX
                    $.ajax({
                        url: '{{ route("admin-global-profit-sharing.detail") }}',
                        method: 'GET',
                        data: {
                            date: date,
                            month: '{{ $month }}'
                        },
                        success: function(response) {
                            if (response.success && response.data) {
                                var html = '<h5 class="mb-3">Detail Global Profit Sharing - ' + response.date_formatted + '</h5>';
                                html += '<div class="table-responsive">';
                                html += '<table class="table table-hover table-striped table-bordered">';
                                html += '<thead><tr><th>#</th><th>Username</th><th class="text-right">Nominal (Rp)</th></tr></thead>';
                                html += '<tbody>';
                                
                                if (response.data.length > 0) {
                                    response.data.forEach(function(item, index) {
                                        html += '<tr>';
                                        html += '<td>' + (index + 1) + '</td>';
                                        html += '<td><a href="' + item.profile_url + '">' + item.username + '</a></td>';
                                        html += '<td class="text-right"><code>' + item.amount_formatted + '</code></td>';
                                        html += '</tr>';
                                    });
                                } else {
                                    html += '<tr><td colspan="3" class="text-center">Tidak ada data</td></tr>';
                                }
                                
                                html += '</tbody>';
                                html += '<tfoot><tr><th colspan="2" class="text-right">Total:</th><th class="text-right"><code>' + response.total_formatted + '</code></th></tr></tfoot>';
                                html += '</table>';
                                html += '</div>';
                                
                                modalBody.html(html);
                            } else {
                                modalBody.html('<div class="alert alert-danger">Gagal memuat data</div>');
                            }
                        },
                        error: function() {
                            modalBody.html('<div class="alert alert-danger">Terjadi kesalahan saat memuat data</div>');
                        }
                    });
                });
            });
        </script>
    @else
        <script>
            jQuery(document).ready(function() {
                // Fungsi untuk menghitung total bonus
                function calculateBonusTotal() {
                    // Parse cashback dengan handle error
                    var cashbackText = $('#cashback').length > 0 ? $('#cashback').text().replace(/\./g, "").replace(/\s/g, "").replace(/[^\d]/g, "") : "0";
                    var cashback = parseFloat(cashbackText);
                    if (isNaN(cashback)) {
                        cashback = 0;
                    }
                    
                    // Parse power plus dengan handle error
                    var powerPlusText = $('#power-plus').length > 0 ? $('#power-plus').text().replace(/\./g, "").replace(/\s/g, "").replace(/[^\d]/g, "") : "0";
                    var powerPlus = parseFloat(powerPlusText);
                    if (isNaN(powerPlus)) {
                        powerPlus = 0;
                    }
                    
                    // Parse profit sharing dengan handle error
                    var profitSharingText = $('#profit-sharing').length > 0 ? $('#profit-sharing').text().replace(/\./g, "").replace(/\s/g, "").replace(/[^\d]/g, "") : "0";
                    var profitSharing = parseFloat(profitSharingText);
                    if (isNaN(profitSharing)) {
                        profitSharing = 0;
                    }
                    
                    // Parse potency dengan handle error - hanya ambil angka, hilangkan HTML
                    var potencyText = "0";
                    if ($('#potency').length > 0) {
                        // Ambil text dari elemen, hilangkan semua karakter non-digit kecuali tanda minus di awal
                        potencyText = $('#potency').text().replace(/\./g, "").replace(/\s/g, "").replace(/[^\d]/g, "");
                        // Jika masih kosong atau hanya whitespace, set ke 0
                        if (!potencyText || potencyText.trim() === '') {
                            potencyText = "0";
                        }
                    }
                    var potency = parseFloat(potencyText);
                    if (isNaN(potency)) {
                        potency = 0;
                    }
                    
                    // Hitung sum: Komisi Penjualan + Bonus Power Plus + Bonus Global Profit Sharing
                    var sum = cashback + powerPlus + profitSharing;
                    
                    // Handle NaN
                    if (isNaN(sum)) {
                        sum = 0;
                    }
                    
                    // Update sum
                    if ($('#sum').length > 0) {
                        $('#sum').html(sum.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
                    }
                    
                    // Hitung administrative
                    var administrative = sum <= 10000 ? 0 : 10000;
                    if ($('#administrative').length > 0) {
                        $('#administrative').html(administrative.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
                    }
                    
                    // Hitung tax
                    var tax = Math.round(sum >= 330000 ? ('{{ Auth::user()->npwp }}' != '' ? (sum * 5 / 100) : (sum * 6 / 100)) : 0);
                    if (isNaN(tax)) {
                        tax = 0;
                    }
                    if ($('#tax').length > 0) {
                        $('#tax').html(tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
                    }
                    
                    // Hitung total
                    var total = sum - administrative - tax;
                    if (isNaN(total)) {
                        total = 0;
                    }
                    if ($('#total').length > 0) {
                        $('#total').html(total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
                    }
                }
                
                // Load potency data
                $.get("/potency/{{ Auth::id() }}?month={{ $month }}", function(data, status) {
                    if (status == 'success' && $('#potency').length > 0) {
                        $('#potency').html(data);
                    } else if ($('#potency').length > 0) {
                        $('#potency').html('0');
                    }
                    // Hitung ulang setelah data dimuat
                    calculateBonusTotal();
                }).fail(function() {
                    if ($('#potency').length > 0) {
                        $('#potency').html('0');
                    }
                    // Hitung ulang meskipun gagal
                    calculateBonusTotal();
                });
                
                // Hitung awal jika elemen sudah ada
                calculateBonusTotal();
                
                // Initialize Bootstrap tooltips
                $('[data-toggle="tooltip"]').tooltip();
                
                var potency = $('#monthly-unilevel-ro').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    order: [
                        [1, "desc"]
                    ],
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
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
                        url: "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
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
                        url: "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                    },
                });
                $('#monthly-profit-sharing').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    order: [
                        [1, "desc"]
                    ],
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                    },
                });
                $('#monthly-profit-sharing-daily').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    order: [
                        [1, "desc"]
                    ],
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                    },
                });
                $('#monthly-power-plus').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    order: [
                        [1, "desc"]
                    ],
                    language: {
                        url: "https://cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
                    },
                });
            });
        </script>
    @endif

    <!-- Modal untuk detail breakdown omset leg -->
    <div class="modal fade" id="leg-omzet-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Omset Grup</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Content akan diisi via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal untuk detail GPS per tanggal -->
    <div class="modal fade" id="gps-detail-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rincian Global Profit Sharing</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Content akan diisi via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
