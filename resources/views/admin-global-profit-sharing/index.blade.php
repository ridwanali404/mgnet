@extends('layout.app')
@section('title', 'Rekap Detail Global Profit Sharing')
@section('style')
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">
    <style>
        .dt-bootstrap4 {
            padding: 0 !important;
        }
        
        .summary-card {
            border-left: 4px solid #007bff;
        }
        
        .summary-card.total {
            border-left-color: #28a745;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Rekap Detail Global Profit Sharing</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Rekap Detail GPS</li>
                </ol>
            </div>
        </div>
        
        <!-- Filter Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin-global-profit-sharing.index') }}" class="form-inline">
                            <div class="form-group mr-3">
                                <label for="date" class="mr-2">Tanggal:</label>
                                <input type="date" name="date" id="date" class="form-control" value="{{ $date }}" required>
                            </div>
                            <div class="form-group mr-3">
                                <label for="start_date" class="mr-2">Dari:</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}" required>
                            </div>
                            <div class="form-group mr-3">
                                <label for="end_date" class="mr-2">Sampai:</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card summary-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Omset</h5>
                        <h3 class="text-primary">
                            Rp {{ number_format(collect($dates)->sum('total_omzet'), 0, ',', '.') }}
                        </h3>
                        <p class="text-muted mb-0">
                            Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card">
                    <div class="card-body">
                        <h5 class="card-title">Total GPS (5%)</h5>
                        <h3 class="text-info">
                            Rp {{ number_format(collect($dates)->sum('total_gps_amount'), 0, ',', '.') }}
                        </h3>
                        <p class="text-muted mb-0">
                            5% dari total omset
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card">
                    <div class="card-body">
                        <h5 class="card-title">Total GPS Dibagikan</h5>
                        <h3 class="text-success">
                            Rp {{ number_format(collect($dates)->sum('total_gps_distributed'), 0, ',', '.') }}
                        </h3>
                        <p class="text-muted mb-0">
                            Total yang sudah dibagikan ke member
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card total">
                    <div class="card-body">
                        <h5 class="card-title">Rata-rata Platinum</h5>
                        <h3 class="text-warning">
                            {{ number_format(collect($dates)->avg('platinum_count'), 1) }}
                        </h3>
                        <p class="text-muted mb-0">
                            Rata-rata platinum aktif per hari
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detail Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Detail Global Profit Sharing Harian</h4>
                        <h6 class="card-subtitle">Breakdown perhitungan GPS per tanggal untuk validasi data</h6>
                        <div class="table-responsive">
                            <table id="gps-detail-table"
                                class="display nowrap table table-hover table-striped table-bordered" cellspacing="0"
                                width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th class="text-right">Omset Harian (Rp)</th>
                                        <th class="text-right">GPS 5% (Rp)</th>
                                        <th class="text-center">Jumlah Platinum</th>
                                        <th class="text-right">GPS per Member (Rp)</th>
                                        <th class="text-right">Total GPS Dibagikan (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dates as $index => $dateData)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><code>{{ $dateData['date_formatted'] }}</code></td>
                                            <td class="text-right">
                                                <code>{{ number_format($dateData['total_omzet'], 0, ',', '.') }}</code>
                                            </td>
                                            <td class="text-right">
                                                <code class="text-info">{{ number_format($dateData['total_gps_amount'], 0, ',', '.') }}</code>
                                            </td>
                                            <td class="text-center">
                                                <code>{{ $dateData['platinum_count'] }}</code>
                                            </td>
                                            <td class="text-right">
                                                <code class="text-success">{{ number_format($dateData['gps_amount_per_member'], 0, ',', '.') }}</code>
                                            </td>
                                            <td class="text-right">
                                                <code class="text-primary">{{ number_format($dateData['total_gps_distributed'], 0, ',', '.') }}</code>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-right"><strong>Total:</strong></th>
                                        <th class="text-right">
                                            <code><strong>Rp {{ number_format(collect($dates)->sum('total_omzet'), 0, ',', '.') }}</strong></code>
                                        </th>
                                        <th class="text-right">
                                            <code class="text-info"><strong>Rp {{ number_format(collect($dates)->sum('total_gps_amount'), 0, ',', '.') }}</strong></code>
                                        </th>
                                        <th class="text-center">
                                            <code><strong>{{ number_format(collect($dates)->avg('platinum_count'), 1) }}</strong></code>
                                        </th>
                                        <th class="text-right">
                                            <code class="text-success"><strong>Rp {{ number_format(collect($dates)->avg('gps_amount_per_member'), 0, ',', '.') }}</strong></code>
                                        </th>
                                        <th class="text-right">
                                            <code class="text-primary"><strong>Rp {{ number_format(collect($dates)->sum('total_gps_distributed'), 0, ',', '.') }}</strong></code>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('material-pro/assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-buttons/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/jszip/dist/jszip.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/pdfmake/build/vfs_fonts.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#gps-detail-table').DataTable({
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
@endsection
