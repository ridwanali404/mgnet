@extends('layout.app')
@section('title', 'Bonus Harian')
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
@php
    $date = request()->date ?? date('Y-m-d');
@endphp
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Bonus Harian</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Bonus Harian</li>
                </ol>
            </div>
            <div class="col-md-7 col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end">
                </div>
            </div>
        </div>
        <form class="form-group" method="GET" action="{{ url('daily2') }}">
            <input class="form-control" type="date" name="date" value="{{ $date }}"
                onchange="this.form.submit()">
        </form>
        @if (Auth::user()->type == 'admin')
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Bonus Harian</h4>
                    <div class="table-responsive">
                        <table id="bonuses" class="display nowrap table table-hover table-striped table-bordered"
                            cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th data-orderable=false>#</th>
                                    <th>Join</th>
                                    <th>Member</th>
                                    <th>Rekening</th>
                                    <th class="text-right">Komisi Sponsor (Rp)</th>
                                    <th class="text-right">Komisi Monoleg (Rp)</th>
                                    <th class="text-right">Komisi Generasi (Rp)</th>
                                    <th class="text-right">Total (Rp)</th>
                                    <th class="text-right">Automaintain (Rp)</th>
                                    <th class="text-right">Pajak (Rp)</th>
                                    <th class="text-right">Biaya Admin (Rp)</th>
                                    <th class="text-right">Ditransfer (Rp)</th>
                                    <th>Status</th>
                                    <th>Dibayar pada</th>
                                    <th class="text-right">Konfirmasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $a)
                                    @php
                                        $today_paid = $a->daily($date)->first()->paid_at;
                                        $days = $today_paid
                                            ? $a
                                                ->bonuses()
                                                ->where('paid_at', $today_paid)
                                                ->get()
                                            : $a->unpaidDaily($date)->get();
                                        $days_group = $days
                                            ->groupBy(function ($item) {
                                                return $item->created_at->format('Y-m-d');
                                            })
                                            ->map(function ($group) use ($daily_admin_fee) {
                                                $group_amount = $group->sum('amount');
                                                return (object) [
                                                    'date' => $group->first()->created_at->format('Y-m-d'),
                                                    'amount' => $group_amount,
                                                    'admin' => $group_amount >= 60000 ? $daily_admin_fee : 0,
                                                    'paid_at' => $group->first()->paid_at,
                                                ];
                                            });
                                        $days_count = $days_group->count();
                                        $days_amount = $days->sum('amount');
                                        $total = $days_amount;
                                        $automaintain = round(0.1 * $total);
                                        $tax = $automaintain >= 330000 ? ($tax = ($automaintain * ($a->npwp ? 5 : 6)) / 100) : 0;
                                        $admin = $days_group->sum('admin');
                                        $total_transfer = $total - $automaintain - $tax - $admin;
                                    @endphp
                                    <tr>
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
                                            <code>{{ number_format($a->daily($date)->where('type', 'Komisi Sponsor')->sum('amount')) }}</code>
                                        </td>
                                        <td class="text-right">
                                            <code>{{ number_format($a->daily($date)->where('type', 'Komisi Monoleg')->sum('amount')) }}</code>
                                        </td>
                                        <td class="text-right">
                                            <code>{{ number_format($a->daily($date)->where('type', 'Bonus Generasi')->sum('amount')) }}</code>
                                        </td>
                                        <td class="text-right">
                                            <code>{{ number_format($total) }}</code>
                                            @if ($days_count > 1)
                                                <br /><small><a href="#detail{{ $a->id }}" data-toggle="modal">Lihat
                                                        hari</a></small>
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
                                            @if ($today_paid)
                                                <span class="label label-success">Sudah dibayar</span>
                                            @elseif ($total_transfer < 50000)
                                                <span class="label label-danger">Bonus belum cukup</span>
                                            @else
                                                <span class="label label-warning">Menunggu pembayaran</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($today_paid)
                                                <code>{{ $today_paid }}</code>
                                                @if ($days_count > 1)
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
                                            @if (!$today_paid && $total_transfer >= 50000)
                                                <button class="btn btn-xs btn-rounded btn-success" data-toggle="modal"
                                                    data-target="#confirm{{ $a->id }}">konfirmasi</button><br />
                                                <code>Rp&nbsp;{{ number_format($total_transfer) }}</code><br />
                                                <small>Total Belum Ditransfer </small>
                                            @endif
                                            @if ($today_paid)
                                                <button class="btn btn-xs btn-rounded btn-danger" data-toggle="modal"
                                                    data-target="#cancel{{ $a->id }}">batalkan</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            @php
                $a = Auth::user();
            @endphp
            @if ($bonuses->first())
                @php
                    $today_paid = $a->daily($date)->first()->paid_at;
                    $days = $today_paid
                        ? $a
                            ->bonuses()
                            ->where('paid_at', $today_paid)
                            ->get()
                        : $a->unpaidDaily($date)->get();
                    $days_group = $days
                        ->groupBy(function ($item) {
                            return $item->created_at->format('Y-m-d');
                        })
                        ->map(function ($group) use ($daily_admin_fee) {
                            $group_amount = $group->sum('amount');
                            return (object) [
                                'date' => $group->first()->created_at->format('Y-m-d'),
                                'amount' => $group_amount,
                                'admin' => $group_amount >= 60000 ? $daily_admin_fee : 0,
                                'paid_at' => $group->first()->paid_at,
                            ];
                        });
                    $days_count = $days_group->count();
                    $days_amount = $days->sum('amount');
                    $total = $days_amount;
                    $automaintain = round(0.1 * $total);
                    $tax = $automaintain >= 330000 ? ($tax = ($automaintain * ($a->npwp ? 5 : 6)) / 100) : 0;
                    $admin = $days_group->sum('admin');
                    $total_transfer = $total - $automaintain - $tax - $admin;
                @endphp
                <div class="card table-responsive">
                    <table class="table table-hover table-stripped m-0">
                        <tr>
                            <td>Komisi Sponsor (Rp)</td>
                            <td class="text-right">
                                <code>{{ number_format($bonuses->where('type', 'Komisi Sponsor')->sum('amount')) }}</code>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Komisi Monoleg (Rp)
                                @if (auth()->user()->isMonoleg() &&
                                        auth()->user()->monolegSponsors()->count())
                                    <label class="mb-0 label label-rounded label-success">aktif</label>
                                @else
                                    <label class="mb-0 label label-rounded label-danger">belum aktif</label>
                                @endif
                            </td>
                            <td class="text-right">
                                <code>{{ number_format($bonuses->where('type', 'Komisi Monoleg')->sum('amount')) }}</code>
                            </td>
                        </tr>
                        <tr>
                            <td>Komisi Generasi (Rp)</td>
                            <td class="text-right">
                                <code>{{ number_format($bonuses->where('type', 'Bonus Generasi')->sum('amount')) }}</code>
                            </td>
                        </tr>
                        <tr>
                            <td>Total (Rp)</td>
                            <td class="text-right">
                                <code>{{ number_format($total) }}</code>
                                @if ($days_count > 1)
                                    <br /><small><a href="#detail{{ $a->id }}" data-toggle="modal">Lihat
                                            hari</a></small>
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
                                @if ($today_paid)
                                    <span class="label label-success">Sudah dibayar</span>
                                @else
                                    <span class="label label-warning">Menunggu pembayaran</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Dibayar pada</td>
                            <td class="text-right">
                                @if ($today_paid)
                                    <code>{{ $today_paid }}</code>
                                    @if ($days_count > 1)
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
                    <h4 class="card-title">Bonus Harian</h4>
                    <div class="table-responsive">
                        <table id="dailyUnilevelBonuses"
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
    </div>
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
                $today_paid = $a->daily($date)->first()->paid_at;
                $days = $today_paid
                    ? $a
                        ->bonuses()
                        ->where('paid_at', $today_paid)
                        ->get()
                    : $a->unpaidDaily($date)->get();
                $days_group = $days
                    ->groupBy(function ($item) {
                        return $item->created_at->format('Y-m-d');
                    })
                    ->map(function ($group) use ($daily_admin_fee) {
                        $group_amount = $group->sum('amount');
                        return (object) [
                            'date' => $group->first()->created_at->format('Y-m-d'),
                            'amount' => $group_amount,
                            'admin' => $group_amount >= 60000 ? $daily_admin_fee : 0,
                            'paid_at' => $group->first()->paid_at,
                        ];
                    });
                $days_count = $days_group->count();
                $days_amount = $days->sum('amount');
                $total = $days_amount;
                $automaintain = round(0.1 * $total);
                $tax = $automaintain >= 330000 ? ($tax = ($automaintain * ($a->npwp ? 5 : 6)) / 100) : 0;
                $admin = $days_group->sum('admin');
                $total_transfer = $total - $automaintain - $tax - $admin;
            @endphp
            @if (Auth::user()->type == 'admin')
                @if (!$today_paid && $total_transfer >= 50000)
                    <form action="{{ url('daily/' . $a->id . '/confirm') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="date" value="{{ $date }}" />
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
                @endif
                @if ($today_paid)
                    <form action="{{ url('daily/' . $a->id . '/cancel') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="paid_at" value="{{ $today_paid }}" />
                        <div class="modal inmodal" id="cancel{{ $a->id }}" tabindex="-1" role="dialog"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content animated fadeInDown">
                                    <div class="modal-body">
                                        <h3>Batal</h3>
                                        <p>Apakah anda yakin?</p>
                                        <div class="text-right">
                                            <button type="submit" class="btn btn-danger btn-rounded">Batalkan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif
            @endif
            @if ($days_count > 1)
                <div class="modal inmodal" id="detail{{ $a->id }}" tabindex="-1" role="dialog"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content animated fadeInDown">
                            <div class="modal-header">
                                <h4 class="modal-title">Lihat Hari</h4>
                                <button type="button" class="close" data-dismiss="modal"><span
                                        aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="table-responsive">
                                    <table class="display nowrap table table-hover m-b-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Tanggal</th>
                                                <th class="text-right">Total (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($days_group as $group)
                                                <tr>
                                                    <td>{{ $loop->index + 1 }}</td>
                                                    <td><code>{{ $group->date }}</code></td>
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
            @if ($today_paid && $days_count > 1)
                <div class="modal inmodal" id="detail-paid{{ $a->id }}" tabindex="-1" role="dialog"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content animated fadeInDown">
                            <div class="modal-header">
                                <h4 class="modal-title">Dibayar Sekaligus</h4>
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
                                                <th class="text-right">Total (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($days_group as $group)
                                                <tr>
                                                    <td>{{ $loop->index + 1 }}</td>
                                                    <td><code>{{ $group->date }}</code></td>
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
                                                            {{ $today_paid }}</code></small>
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
            $('#bonuses').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
                },
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                order: [
                    [1, 'asc']
                ],
            });
            $('#dailyUnilevelBonuses').DataTable({
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
