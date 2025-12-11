@extends('layout.app')
@section('title', 'Trip')
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
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Trip</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Trip</li>
                </ol>
            </div>
            <div class="col-md-7 col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end">
                    @if (Auth::user()->type == 'admin')
                        <button type="button" class="btn waves-effect waves-light btn-danger" data-toggle="modal" data-target="#generateTripModal">
                            <i class="mdi mdi-refresh"></i> Generate Bonus Trip
                        </button>
                    @endif
                </div>
            </div>
        </div>
        
        @if (Auth::user()->type == 'admin')
            <!-- Filter Tanggal -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('userTrip.index') }}" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="date" class="mr-2">Filter Tanggal:</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="{{ $selectedDate ?? \Carbon\Carbon::yesterday()->format('Y-m-d') }}" 
                                   onchange="this.form.submit()">
                        </div>
                        <small class="text-muted">Default: H-1 (Hari Kemarin)</small>
                    </form>
                </div>
            </div>
            
            @if (isset($dailyInfo) && $dailyInfo)
                <!-- Informasi Harian -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Informasi Bonus Trip - {{ $dailyInfo['date_readable'] }}</h3>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h4 class="text-white">Omset Nasional</h4>
                                        <h2 class="text-white">Rp {{ number_format($dailyInfo['total_omzet'], 0, ',', '.') }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h4 class="text-white">4% dari Omset</h4>
                                        <h2 class="text-white">Rp {{ number_format($dailyInfo['total_umroh_amount'], 0, ',', '.') }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h4 class="text-white">Member Qualified</h4>
                                        <h2 class="text-white">{{ $dailyInfo['qualified_count'] }} orang</h2>
                                        @if ($dailyInfo['qualified_count'] > 0)
                                            <p class="text-white-50 mb-0">Bonus per Member: Rp {{ number_format($dailyInfo['amount_per_member'], 0, ',', '.') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detail Per Member -->
                @if ($dailyInfo['qualified_count'] > 0)
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">Perhitungan Per Member - {{ $dailyInfo['date_readable'] }}</h3>
                            <h6 class="card-subtitle">Rumus: 4% x Omset Nasional : Jumlah Qualified = Bonus per Member</h6>
                            <div class="table-responsive mt-3">
                                <table class="table table-hover table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Member</th>
                                            <th class="text-right">Bonus (Rp)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($dailyInfo['member_details'] as $index => $detail)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $detail['user']->username }}</td>
                                                <td class="text-right"><code>{{ number_format($detail['amount'], 0, ',', '.') }}</code></td>
                                                <td>
                                                    @if ($detail['has_data'])
                                                        <span class="badge badge-success">Sudah di-generate</span>
                                                    @else
                                                        <span class="badge badge-warning">Belum di-generate</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-right">Total:</th>
                                            <th class="text-right">{{ number_format(collect($dailyInfo['member_details'])->sum('amount'), 0, ',', '.') }}</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        @endif
        
        @if (Auth::user()->type == 'member')
            @php
                $currentYear = date('Y');
                $currentYearSaving = $userTripSavings->where('year', $currentYear)->first();
                $totalAccumulation = $currentYearSaving ? $currentYearSaving->yearly_accumulation : 0;
                $claimedAmount = $currentYearSaving ? $currentYearSaving->claimed_amount : 0;
                $availableBalance = $totalAccumulation - $claimedAmount;
                $minClaimAmount = 5000000; // Minimum 5 juta untuk klaim
                $canClaim = $availableBalance >= $minClaimAmount;
            @endphp
            
            @if (isset($isQualified) && !$isQualified)
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-warning" role="alert">
                            <h4 class="alert-heading"><i class="mdi mdi-alert-circle"></i> Belum Memenuhi Syarat</h4>
                            <p>Anda belum memenuhi syarat untuk mendapatkan bonus Trip. Syarat yang harus dipenuhi:</p>
                            <hr>
                            <ul class="mb-0">
                                <li>Memiliki paket <strong>Gold</strong> atau <strong>Platinum</strong></li>
                                <li>Status akun <strong>aktif</strong></li>
                                <li>Memiliki minimal <strong>3 sponsor langsung</strong> yang premium (Gold/Platinum) dan aktif</li>
                            </ul>
                            @if (isset($qualificationReasons) && count($qualificationReasons) > 0)
                                <hr>
                                <p class="mb-0"><strong>Alasan belum qualified:</strong></p>
                                <ul class="mb-0">
                                    @foreach ($qualificationReasons as $reason)
                                        <li>{{ $reason }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Saldo Trip</h3>
                    <h6 class="card-subtitle">Tabungan Umroh/Trip tahun {{ $currentYear }}</h6>
                    @if (isset($isQualified) && !$isQualified)
                        <div class="alert alert-info mb-3">
                            <i class="mdi mdi-information"></i> Setelah memenuhi syarat, bonus Trip akan mulai terakumulasi setiap hari.
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h4 class="text-white">Total Akumulasi</h4>
                                    <h2 class="text-white">Rp {{ number_format($totalAccumulation, 0, ',', '.') }}</h2>
                                    <p class="text-white-50 mb-0">Maksimal Rp 50.000.000/tahun</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h4 class="text-white">Sudah Diklaim</h4>
                                    <h2 class="text-white">Rp {{ number_format($claimedAmount, 0, ',', '.') }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card {{ $canClaim ? 'bg-success' : 'bg-secondary' }} text-white">
                                <div class="card-body">
                                    <h4 class="text-white">Saldo Tersedia</h4>
                                    <h2 class="text-white">Rp {{ number_format($availableBalance, 0, ',', '.') }}</h2>
                                    @if ($canClaim)
                                        <p class="text-white-50 mb-0">✓ Siap untuk diklaim</p>
                                    @else
                                        <p class="text-white-50 mb-0">Minimal Rp {{ number_format($minClaimAmount, 0, ',', '.') }} untuk klaim</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Histori Bonus Trip</h3>
                <h6 class="card-subtitle">
                    Histori bonus Trip harian (4% dari omset nasional dibagi member qualified)
                    @if (Auth::user()->type == 'admin' && isset($selectedDate))
                        - {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
                    @endif
                </h6>
                @if (Auth::user()->type == 'member' && isset($isQualified) && !$isQualified)
                    <div class="alert alert-warning mb-3">
                        <i class="mdi mdi-information"></i> Anda belum memenuhi syarat, sehingga belum ada bonus Trip yang masuk.
                    </div>
                @endif
                <div class="table-responsive">
                    <table id="userTripDailies" class="display nowrap table table-hover table-striped table-bordered"
                        cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                @if (Auth::user()->type == 'admin')
                                    <th>Member</th>
                                @endif
                                <th class="text-right">Bonus (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($userTripDailies->count() > 0)
                                @foreach ($userTripDailies as $a)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($a->date)->translatedFormat('d/m/Y') }}</td>
                                        @if (Auth::user()->type == 'admin')
                                            <td>{{ $a->user->username }}</td>
                                        @endif
                                        <td class="text-right"><code>{{ number_format($a->amount, 0, ',', '.') }}</code></td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="{{ Auth::user()->type == 'admin' ? '4' : '3' }}" class="text-center">
                                        <em>Belum ada data bonus Trip</em>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        @if ($userTripDailies->count() > 0)
                            <tfoot>
                                <tr>
                                    <th colspan="{{ Auth::user()->type == 'admin' ? '3' : '2' }}" class="text-right">Total:</th>
                                    <th class="text-right">{{ number_format($userTripDailies->sum('amount'), 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        @if (Auth::user()->type == 'admin')
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Rekap Tabungan Trip per Tahun</h3>
                    <h6 class="card-subtitle">Akumulasi tabungan Trip per member per tahun</h6>
                    <div class="table-responsive">
                        <table id="userTripSavings" class="display nowrap table table-hover table-striped table-bordered"
                            cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Member</th>
                                    <th>Tahun</th>
                                    <th class="text-right">Akumulasi (Rp)</th>
                                    <th class="text-right">Diklaim (Rp)</th>
                                    <th class="text-right">Tersedia (Rp)</th>
                                    <th>Tim Aktif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($userTripSavings as $a)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>{{ $a->user->username }}</td>
                                        <td>{{ $a->year }}</td>
                                        <td class="text-right"><code>{{ number_format($a->yearly_accumulation, 0, ',', '.') }}</code></td>
                                        <td class="text-right"><code>{{ number_format($a->claimed_amount, 0, ',', '.') }}</code></td>
                                        <td class="text-right"><code>{{ number_format($a->yearly_accumulation - $a->claimed_amount, 0, ',', '.') }}</code></td>
                                        <td class="text-center">{{ $a->active_teams_count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
        
        @if (Auth::user()->type == 'admin')
            <!-- Modal Generate Trip Bonus -->
            <div class="modal fade" id="generateTripModal" tabindex="-1" role="dialog" aria-labelledby="generateTripModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="generateTripModalLabel">Generate Bonus Trip</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="generateTripForm">
                            @csrf
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information"></i> 
                                    Generate akan memproses dari tanggal terakhir yang sudah di-generate sampai tanggal yang dipilih, 
                                    dan akan regenerate tanggal terakhir untuk memastikan data terbaru.
                                </div>
                                <div class="form-group">
                                    <label for="generateDate">Tanggal Target <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="generateDate" name="date" 
                                           value="{{ \Carbon\Carbon::yesterday()->format('Y-m-d') }}" required>
                                    <small class="form-text text-muted">Default: Hari kemarin (H-1)</small>
                                </div>
                                <div id="generateResult" style="display: none;"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger" id="generateBtn">
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    Generate
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
@section('script')
    <script src="{{ asset('material-pro/assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('material-pro/assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#userTripDailies').DataTable({
                "order": [[1, "desc"]],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                }
            });
            @if (Auth::user()->type == 'admin')
                $('#userTripSavings').DataTable({
                    "order": [[2, "desc"]],
                    "language": {
                        "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                    }
                });
            @endif
        });
        
        @if (Auth::user()->type == 'admin')
            // Handle Generate Trip Bonus
            $('#generateTripForm').on('submit', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const submitBtn = $('#generateBtn');
                const spinner = submitBtn.find('.spinner-border');
                const resultDiv = $('#generateResult');
                
                // Disable button and show spinner
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');
                resultDiv.hide().html('');
                
                $.ajax({
                    url: '{{ route("userTrip.generate") }}',
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        spinner.addClass('d-none');
                        submitBtn.prop('disabled', false);
                        
                        if (response.success) {
                            resultDiv.removeClass('alert-danger').addClass('alert alert-success').show().html(
                                '<h6><i class="mdi mdi-check-circle"></i> Generate Berhasil!</h6>' +
                                '<p>' + response.message + '</p>' +
                                (response.summary ? '<pre style="white-space: pre-wrap; font-size: 12px;">' + response.summary + '</pre>' : '')
                            );
                            
                            // Reload page after 2 seconds
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            resultDiv.removeClass('alert-success').addClass('alert alert-danger').show().html(
                                '<h6><i class="mdi mdi-alert-circle"></i> Error!</h6>' +
                                '<p>' + (response.message || 'Terjadi kesalahan saat generate') + '</p>'
                            );
                        }
                    },
                    error: function(xhr) {
                        spinner.addClass('d-none');
                        submitBtn.prop('disabled', false);
                        
                        let errorMessage = 'Terjadi kesalahan saat generate';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        resultDiv.removeClass('alert-success').addClass('alert alert-danger').show().html(
                            '<h6><i class="mdi mdi-alert-circle"></i> Error!</h6>' +
                            '<p>' + errorMessage + '</p>'
                        );
                    }
                });
            });
        @endif
    </script>
@endsection
