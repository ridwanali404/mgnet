@extends('layout.app')
@section('title', 'Report Omset')
@section('style')
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}"
        rel="stylesheet">
    <link href="{{ asset('material-pro/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css') }}"
        rel="stylesheet">
    <style>
        .dt-bootstrap4 {
            padding: 0 !important;
        }
        
        .nav-tabs .nav-link {
            color: #495057;
        }
        
        .nav-tabs .nav-link.active {
            color: #007bff;
            font-weight: 600;
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
                <h3 class="text-themecolor m-b-0 m-t-0">Report Omset</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Report Omset</li>
                </ol>
            </div>
        </div>
        
        <!-- Filter Form -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ url('report-omset') }}" class="form-inline">
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
        
        <!-- Tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#omset-harian" role="tab">
                    <span class="hidden-sm-up"><i class="mdi mdi-cash-multiple"></i></span>
                    <span class="hidden-xs-down">Omset Harian</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#total-bonus-harian" role="tab">
                    <span class="hidden-sm-up"><i class="mdi mdi-gift"></i></span>
                    <span class="hidden-xs-down">Total Bonus Harian</span>
                </a>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content tabcontent-border p-3">
            <!-- Omset Harian Tab -->
            <div class="tab-pane fade show active" id="omset-harian" role="tabpanel">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card summary-card">
                            <div class="card-body">
                                <h5 class="card-title">Omset Hari Ini</h5>
                                <h3 class="text-primary">
                                    Rp {{ number_format($omsetHarian->total_omset ?? 0, 0, ',', '.') }}
                                </h3>
                                <p class="text-muted mb-0">
                                    {{ $omsetHarian->jumlah_pin ?? 0 }} pin terjual
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card summary-card total">
                            <div class="card-body">
                                <h5 class="card-title">Total Omset Semua</h5>
                                <h3 class="text-success">
                                    Rp {{ number_format($totalOmsetSemua, 0, ',', '.') }}
                                </h3>
                                <p class="text-muted mb-0">Dari semua penjualan pin</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Omset Harian ({{ \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->translatedFormat('d F Y') }})</h4>
                        <div class="table-responsive">
                            <table id="omset-harian-table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th class="text-right">Jumlah Pin</th>
                                        <th class="text-right">Sub Total Omset (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $counter = 1;
                                        $grandTotal = 0;
                                    @endphp
                                    @foreach ($omsetHarianRange as $omset)
                                        @php
                                            $grandTotal += $omset->total_omset;
                                        @endphp
                                        <tr>
                                            <td>{{ $counter++ }}</td>
                                            <td>
                                                <a href="javascript:void(0)" class="omset-detail-link" data-date="{{ $omset->tanggal }}">
                                                    {{ \Carbon\Carbon::createFromFormat('Y-m-d', $omset->tanggal)->translatedFormat('d F Y') }}
                                                </a>
                                            </td>
                                            <td class="text-right">{{ number_format($omset->jumlah_pin, 0, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format($omset->total_omset, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total:</th>
                                        <th class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total Bonus Harian Tab -->
            <div class="tab-pane fade" id="total-bonus-harian" role="tabpanel">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card summary-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Bonus Hari Ini</h5>
                                <h3 class="text-primary">
                                    Rp {{ number_format($totalBonusHarian->total_bonus ?? 0, 0, ',', '.') }}
                                </h3>
                                <p class="text-muted mb-0">
                                    {{ $totalBonusHarian->jumlah_bonus ?? 0 }} bonus
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card summary-card total">
                            <div class="card-body">
                                <h5 class="card-title">Total Bonus Semua</h5>
                                <h3 class="text-success">
                                    Rp {{ number_format($totalBonusSemua, 0, ',', '.') }}
                                </h3>
                                <p class="text-muted mb-0">Dari semua bonus yang pernah dibagikan</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Total Bonus Harian ({{ \Carbon\Carbon::createFromFormat('Y-m-d', $startDate)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->translatedFormat('d F Y') }})</h4>
                        <div class="table-responsive">
                            <table id="total-bonus-harian-table" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Tanggal</th>
                                        <th class="text-right">Jumlah Bonus</th>
                                        <th class="text-right">Total Bonus (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $counter = 1;
                                        $grandTotalBonus = 0;
                                    @endphp
                                    @foreach ($totalBonusHarianRange as $bonus)
                                        @php
                                            $grandTotalBonus += $bonus->total_bonus;
                                        @endphp
                                        <tr>
                                            <td>{{ $counter++ }}</td>
                                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $bonus->tanggal)->translatedFormat('d F Y') }}</td>
                                            <td class="text-right">{{ number_format($bonus->jumlah_bonus, 0, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format($bonus->total_bonus, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total:</th>
                                        <th class="text-right">Rp {{ number_format($grandTotalBonus, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal untuk Detail Omset -->
    <div class="modal fade" id="omsetDetailModal" tabindex="-1" role="dialog" aria-labelledby="omsetDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="omsetDetailModalLabel">Detail Omset</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="omset-detail-loading" class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <div id="omset-detail-content" style="display: none;">
                        <h6 class="mb-3">Tanggal: <span id="detail-date"></span></h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h5 class="card-title text-success">HASIL PENJUALAN</h5>
                                        <h4 class="text-success" id="total-penjualan">Rp 0</h4>
                                        <p class="text-muted mb-0" id="count-penjualan">0 pin</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <h5 class="card-title text-info">HASIL AUTO RO</h5>
                                        <h4 class="text-info" id="total-auto-ro">Rp 0</h4>
                                        <p class="text-muted mb-0" id="count-auto-ro">0 pin</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Detail Hasil Penjualan:</h6>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Paket</th>
                                                <th class="text-right">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody id="penjualan-detail">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Detail Hasil AUTO RO:</h6>
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Paket</th>
                                                <th class="text-right">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody id="auto-ro-detail">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('material-pro/assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#omset-harian-table').DataTable({
                "order": [[1, "desc"]],
                "pageLength": 25,
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                }
            });
            
            $('#total-bonus-harian-table').DataTable({
                "order": [[1, "desc"]],
                "pageLength": 25,
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                }
            });
            
            // Handle click on omset detail link
            $(document).on('click', '.omset-detail-link', function(e) {
                e.preventDefault();
                var date = $(this).data('date');
                $('#omsetDetailModal').modal('show');
                loadOmsetDetail(date);
            });
            
            function loadOmsetDetail(date) {
                $('#omset-detail-loading').show();
                $('#omset-detail-content').hide();
                
                $.ajax({
                    url: '{{ url("report-omset/breakdown") }}',
                    method: 'GET',
                    data: { date: date },
                    success: function(response) {
                        $('#omset-detail-loading').hide();
                        $('#omset-detail-content').show();
                        
                        // Set date
                        var dateObj = new Date(response.date);
                        var formattedDate = dateObj.toLocaleDateString('id-ID', { 
                            weekday: 'long', 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        });
                        $('#detail-date').text(formattedDate);
                        
                        // Set totals
                        $('#total-penjualan').text('Rp ' + formatNumber(response.total_penjualan));
                        $('#total-auto-ro').text('Rp ' + formatNumber(response.total_auto_ro));
                        $('#count-penjualan').text(response.hasil_penjualan.length + ' pin');
                        $('#count-auto-ro').text(response.hasil_auto_ro.length + ' pin');
                        
                        // Set detail penjualan
                        var penjualanHtml = '';
                        if (response.hasil_penjualan.length > 0) {
                            response.hasil_penjualan.forEach(function(item) {
                                penjualanHtml += '<tr>' +
                                    '<td>' + item.username + '</td>' +
                                    '<td>' + item.pin_name + '</td>' +
                                    '<td class="text-right">Rp ' + formatNumber(item.amount) + '</td>' +
                                    '</tr>';
                            });
                        } else {
                            penjualanHtml = '<tr><td colspan="3" class="text-center text-muted">Tidak ada data</td></tr>';
                        }
                        $('#penjualan-detail').html(penjualanHtml);
                        
                        // Set detail auto RO
                        var autoROHtml = '';
                        if (response.hasil_auto_ro.length > 0) {
                            response.hasil_auto_ro.forEach(function(item) {
                                autoROHtml += '<tr>' +
                                    '<td>' + item.username + '</td>' +
                                    '<td>' + item.pin_name + '</td>' +
                                    '<td class="text-right">Rp ' + formatNumber(item.amount) + '</td>' +
                                    '</tr>';
                            });
                        } else {
                            autoROHtml = '<tr><td colspan="3" class="text-center text-muted">Tidak ada data</td></tr>';
                        }
                        $('#auto-ro-detail').html(autoROHtml);
                    },
                    error: function() {
                        $('#omset-detail-loading').hide();
                        alert('Terjadi kesalahan saat memuat data detail omset.');
                    }
                });
            }
            
            function formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        });
    </script>
@endsection

