@extends('layout.app')
@section('title', 'Dashboard')
@section('style')
    <link href="{{ asset('tree/style.css') }}" rel="stylesheet">
@endsection
@php
    $platinumRewards = Auth::user()
        ->userRewards()
        ->whereHas('reward', function ($q) {
            $q->where('is_platinum', true);
        })
        ->get();
    $nonPlatinumRewards = Auth::user()
        ->userRewards()
        ->whereHas('reward', function ($q) {
            $q->where('is_platinum', false);
        })
        ->get();
@endphp
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Dashboard</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
            <div class="col-md-7 col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card card-inverse card-cr">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="m-r-20 align-self-center">
                                <h1 class="text-white"><i class="mdi mdi-crown"></i></h1>
                            </div>
                            <div style="width: calc(100% - (20px + 36px));">
                                <h3 class="card-title text-truncate">Peringkat</h3>
                                <h6 class="card-subtitle text-truncate">Peringkat {{ Auth::user()->username }}</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 align-self-center">
                                <h2 class="font-light text-white text-truncate">
                                    @if (Auth::user()->type == 'admin')
                                        Administrator
                                    @else
                                        @php
                                            $userReward = Auth::user()
                                                ->userRewards()
                                                ->latest()
                                                ->first();
                                        @endphp
                                        @if ($userReward)
                                            @if ($userReward->reward->is_platinum)
                                                BSM
                                                @foreach ($platinumRewards as $a)
                                                    <i class="mdi mdi-diamond text-info"></i>
                                                @endforeach
                                            @else
                                                BSM
                                                @foreach ($nonPlatinumRewards as $a)
                                                    <i class="mdi mdi-star text-danger"></i>
                                                @endforeach
                                            @endif
                                        @else
                                            -
                                        @endif
                                    @endif
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if (false)
                <div class="col-lg-4 col-md-4">
                    <div class="card card-inverse card-cr">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="m-r-20 align-self-center">
                                    <h1 class="text-white"><i class="mdi mdi-cards"></i></h1>
                                </div>
                                <div style="width: calc(100% - (20px + 36px));">
                                    <h3 class="card-title text-truncate">Fast Track</h3>
                                    <h6 class="card-subtitle text-truncate">Status Fast Track Minggu Ini</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 align-self-center">
                                    <h2 class="font-light text-white text-truncate">
                                        {{ Auth::user()->isWeekActive(date('Y-\WW')) ? 'Aktif' : 'Belum Aktif' }}
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-lg-12 col-md-12">
                <div class="card card-inverse card-cr">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="m-r-20 align-self-center">
                                <h1 class="text-white"><i class="mdi mdi-calendar-clock"></i></h1>
                            </div>
                            <div style="width: calc(100% - (20px + 36px));">
                                <h3 class="card-title text-truncate">Masa Aktif</h3>
                                <h6 class="card-subtitle text-truncate">Status Keanggotaan Aktif</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 align-self-center">
                                @php
                                    $user = Auth::user();
                                    $activeUntil = $user->active_until;
                                    $isActive = $user->is_active;
                                @endphp
                                @if ($activeUntil)
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $activeDate = \Carbon\Carbon::parse($activeUntil);
                                        $daysLeft = $now->diffInDays($activeDate, false);
                                    @endphp
                                    <h2 class="font-light text-white text-truncate">
                                        @if ($isActive && $daysLeft > 0)
                                            <span class="label label-success">Aktif</span>
                                            <br>
                                            <small class="text-white-50">Sampai: {{ $activeDate->format('d M Y') }}</small>
                                            <br>
                                            <small class="text-white-50">{{ $daysLeft }} hari tersisa</small>
                                        @elseif ($isActive && $daysLeft <= 0)
                                            <span class="label label-warning">Hari Terakhir</span>
                                            <br>
                                            <small class="text-white-50">Berakhir: {{ $activeDate->format('d M Y') }}</small>
                                        @else
                                            <span class="label label-danger">Tidak Aktif</span>
                                            <br>
                                            <small class="text-white-50">Berakhir: {{ $activeDate->format('d M Y') }}</small>
                                        @endif
                                    </h2>
                                @else
                                    <h2 class="font-light text-white text-truncate">
                                        <span class="label label-default">Belum Terdaftar</span>
                                    </h2>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-5 col-md-5">
                <div class="card card-inverse card-cr">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="m-r-20 align-self-center">
                                <h1 class="text-white"><i class="mdi mdi-account-multiple"></i></h1>
                            </div>
                            <div style="width: calc(100% - (20px + 36px));">
                                <h3 class="card-title text-truncate">Referral</h3>
                                <h6 class="card-subtitle text-truncate">Jumlah Referral</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 align-self-center">
                                <h2 class="font-light text-white text-truncate">
                                    {{ number_format(Auth::user()->sponsors()->count(),0,',','.') }}
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 col-md-7">
                <div class="card card-inverse card-cr">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="m-r-20 align-self-center">
                                <h1 class="text-white"><i class="mdi mdi-gift"></i></h1>
                            </div>
                            <div style="width: calc(100% - (20px + 36px));">
                                <h3 class="card-title text-truncate">Bonus</h3>
                                <h6 class="card-subtitle text-truncate">Total Bonus</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 align-self-center">
                                <h2 class="font-light text-white text-truncate">
                                    {{ number_format(Auth::user()->bonuses()->sum('amount'),0,',','.') }}
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if (false)
            @if (Auth::user()->current_reward)
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-row">
                            <div class="round round-lg align-self-center round-{{ Auth::user()->current_reward_color }}"><i
                                    class="mdi mdi-crown"></i></div>
                            <div class="m-l-10 align-self-center">
                                <h3 class="m-b-0 font-light">{{ Auth::user()->current_reward }}</h3>
                                <h5 class="text-muted m-b-0">Reward</h5>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
        <div class="row">
            @if (false)
                <div class="col-lg-4 col-md-6">
                    <div class="card card-inverse card-danger">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="m-r-20 align-self-center">
                                    <h1 class="text-white"><i class="mdi mdi-coin"></i></h1>
                                </div>
                                <div style="width: calc(100% - (20px + 36px));">
                                    <h3 class="card-title text-truncate">Total Bonus</h3>
                                    <h6 class="card-subtitle text-truncate">Total Bonus Anda</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 align-self-center">
                                    <h2 class="font-light text-white text-truncate">
                                        {{ number_format(Auth::user()->weeklyBonuses(date('Y-\WW'))->where('is_paid', true)->sum('amount'),0,',','.') }}
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card card-inverse card-warning">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="m-r-20 align-self-center">
                                    <h1 class="text-white"><i class="mdi mdi-account-multiple"></i></h1>
                                </div>
                                <div style="width: calc(100% - (20px + 36px));">
                                    <h3 class="card-title text-truncate">Total Downline</h3>
                                    <h6 class="card-subtitle text-truncate">Free | Premium</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 align-self-center">
                                    <h2 class="font-light text-white text-center text-truncate">
                                        {{ number_format(Auth::user()->freeReferrals()->count(),0,',','.') }}</h2>
                                </div>
                                <div class="col-6 align-self-center">
                                    <h2 class="font-light text-white text-center text-truncate">
                                        {{ number_format(Auth::user()->premiumReferrals()->count(),0,',','.') }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card card-inverse card-success">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="m-r-20 align-self-center">
                                    <h1 class="text-white"><i class="mdi mdi-crown"></i></h1>
                                </div>
                                <div style="width: calc(100% - (20px + 36px));">
                                    <h3 class="card-title text-truncate">Total Downline</h3>
                                    <h6 class="card-subtitle text-truncate">Agen | Distributor</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 align-self-center">
                                    <h2 class="font-light text-white text-center text-truncate">
                                        {{ number_format(Auth::user()->agenReferrals()->count(),0,',','.') }}</h2>
                                </div>
                                <div class="col-6 align-self-center">
                                    <h2 class="font-light text-white text-center text-truncate">
                                        {{ number_format(Auth::user()->distributorReferrals()->count(),0,',','.') }}
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if (false)
                <div class="col-lg-4 col-md-4">
                    <div class="card card-inverse card-cr">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="m-r-20 align-self-center">
                                    <h1 class="text-white"><i class="mdi mdi-crown"></i></h1>
                                </div>
                                <div style="width: calc(100% - (20px + 36px));">
                                    <h3 class="card-title text-truncate">Peringkat</h3>
                                    <h6 class="card-subtitle text-truncate">Peringkat {{ Auth::user()->username }}</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 align-self-center">
                                    <h2 class="font-light text-white text-truncate">
                                        {{ Auth::user()->member->member_phase_name ?? 'Administrator' }}
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-lg-7 col-md-7">
                <a href="{{ url('monthly') }}">
                    <div class="card card-inverse card-cr">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="m-r-20 align-self-center">
                                    <h1 class="text-white"><i class="mdi mdi-gift"></i></h1>
                                </div>
                                <div style="width: calc(100% - (20px + 36px));">
                                    <h3 class="card-title text-truncate">Estimasi Bonus RO Bulan ini</h3>
                                    <h6 class="card-subtitle text-truncate">Klik untuk lihat detail</h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 align-self-center">
                                    <h2 class="font-light text-white text-truncate">
                                        <div id="potency">
                                            <div class="spinner-grow" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </div>
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-5 col-md-5">
                <div class="card card-inverse card-cr">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="m-r-20 align-self-center">
                                <h1 class="text-white"><i class="mdi mdi-crown"></i></h1>
                            </div>
                            <div style="width: calc(100% - (20px + 36px));">
                                <h3 class="card-title text-truncate">PV</h3>
                                <h6 class="card-subtitle text-truncate">Total PV Bulan
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', date('Y-m'))->translatedFormat('F') }}</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 align-self-center">
                                <h2 class="font-light text-white text-truncate">
                                    {{ number_format(Auth::user()->monthlyPoin(date('Y-m')), 0, ',', '.') }}
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if (false)
                <div class="col-lg-6 col-md-6">
                    <a href="{{ url('weekly') }}">
                        <div class="card card-inverse card-primary">
                            <div class="card-body">
                                <div class="d-flex">
                                    <div class="m-r-20 align-self-center">
                                        <h1 class="text-white"><i class="mdi mdi-gift"></i></h1>
                                    </div>
                                    <div style="width: calc(100% - (20px + 36px));">
                                        <h3 class="card-title text-truncate">Bonus Minggu ini</h3>
                                        <h6 class="card-subtitle text-truncate">Klik untuk lihat detail</h6>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 align-self-center">
                                        <h2 class="font-light text-white text-truncate">
                                            {{ number_format(Auth::user()->weeklyBonuses(date('Y-\WW'))->sum('amount'),0,',','.') }}
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
        </div>
        @if (Auth::user()->currentUserPackage)
            <div class="card-group">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <h3 class="m-b-15">
                                    Rp&nbsp;{{ number_format(Auth::user()->currentUserPackage->amount, 0, ',', '.') }}
                                    {{ Auth::user()->currentUserPackage->package->poin_sharing }}</h3>
                                <h6 class="card-subtitle">{{ Auth::user()->currentUserPackage->package->name }}</h6>
                            </div>
                            <div class="col-12">
                                <div class="progress">
                                    <div id="bar-volume" class="progress-bar bg-{{ Auth::user()->color }}"
                                        role="progressbar" style="width: 100%; height: 6px;" aria-valuenow="25"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (Auth::user()->type != 'admin')
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button"
                                class="btn btn-light-secondary text-secondary font-weight-medium dropdown-toggle phase"
                                data-toggle="dropdown">
                                Basic Mitra Q
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="javascript:phase('User Q')">Basic Mitra Q</a>
                                {{-- <a class="dropdown-item" href="javascript:phase('User Q')">Peringkat User Q</a>
                                <a class="dropdown-item" href="javascript:phase('Star Seller')">Peringkat Star Seller</a>
                                <a class="dropdown-item" href="javascript:phase('Reseller')">Peringkat Reseller</a>
                                <a class="dropdown-item" href="javascript:phase('Agen')">Peringkat Agen</a>
                                <a class="dropdown-item" href="javascript:phase('Distributor')">Peringkat Distributor</a> --}}
                            </div>
                        </div>
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <div class="tree text-center"></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">
                        Halaman Depan
                        <a class="text-info float-right" onclick="copy1()" style="cursor: pointer;"><i
                                class="mdi mdi-content-copy"></i></a>
                    </h3>
                    <input id="referral-1" type="hidden" value="{{ url('/?sponsor=' . Auth::user()->username) }}" />
                    <code style="white-space: unset;">{{ url('/?sponsor=' . Auth::user()->username) }}</code>
                    <hr />
                    <h3 class="card-title">
                        Halaman Belanja
                        <a class="text-info float-right" onclick="copy2()" style="cursor: pointer;"><i
                                class="mdi mdi-content-copy"></i></a>
                    </h3>
                    <input id="referral-2" type="hidden"
                        value="{{ url('product/?sponsor=' . Auth::user()->username) }}" />
                    <code style="white-space: unset;">{{ url('product/?sponsor=' . Auth::user()->username) }}</code>
                    <hr />
                    <h3 class="card-title">
                        Halaman Registrasi
                        <a class="text-info float-right" onclick="copy3()" style="cursor: pointer;"><i
                                class="mdi mdi-content-copy"></i></a>
                    </h3>
                    <input id="referral-3" type="hidden"
                        value="{{ url('register/?sponsor=' . Auth::user()->username) }}" />
                    <code style="white-space: unset;">{{ url('register/?sponsor=' . Auth::user()->username) }}</code>
                </div>
            </div>
            @if (App\Models\News::exists())
                <div class="card">
                    <div class="card-body table-responsive p-0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Judul</th>
                                    <th>Video</th>
                                    <th>Download File</th>
                                    <th>Isi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (App\Models\News::latest()->get() as $a)
                                    <tr>
                                        <td><code>{{ $a->created_at }}</code></td>
                                        <td>{{ $a->title }}</td>
                                        <td class="text-nowrap">
                                            @if ($a->youtube)
                                                <iframe width="560" height="315" src="{{ $a->youtube }}"
                                                    title="YouTube video player" frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen></iframe>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            @if ($a->file)
                                                <a href="{{ url('storage/' . $a->file) }}" target="_blank"><i
                                                        class="mdi mdi-download text-inverse"></i> </a>
                                            @endif
                                        </td>
                                        <td>{!! $a->content !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
@section('script')
    @if (env('APP_ENV') == 'production')
        <script>
            $(document).ready(function() {
                $.get("/potency/{{ Auth::id() }}", function(data, status) {
                    if (status == 'success') {
                        $('#potency').html(data);
                    } else {
                        $('#potency').html('Data gagal dimuat');
                    }
                }).fail(function() {
                    $('#potency').html('Data gagal dimuat');
                });
            });
        </script>
    @else
        <script>
            $('#potency').html(0);
        </script>
    @endif
    @if (Auth::user()->type != 'admin')
        <script>
            function copy1() {
                $("#referral-1").attr("type", "text").select();
                document.execCommand("copy");
                $.toast({
                    heading: 'Success',
                    text: 'Link copied',
                    showHideTransition: 'slide',
                    icon: 'success'
                });
                $("#referral-1").attr("type", "hidden");
            }

            function copy2() {
                $("#referral-2").attr("type", "text").select();
                document.execCommand("copy");
                $.toast({
                    heading: 'Success',
                    text: 'Link copied',
                    showHideTransition: 'slide',
                    icon: 'success'
                });
                $("#referral-2").attr("type", "hidden");
            }

            function copy3() {
                $("#referral-3").attr("type", "text").select();
                document.execCommand("copy");
                $.toast({
                    heading: 'Success',
                    text: 'Link copied',
                    showHideTransition: 'slide',
                    icon: 'success'
                });
                $("#referral-3").attr("type", "hidden");
            }
        </script>
    @endif
    @if (Auth::user()->currentUserPackage)
        <script>
            $(document).ready(function() {
                $("#bar-volume").css("width",
                    "{{ (Auth::user()->currentUserPackage->amount / (Auth::user()->currentUserPackage->package->price * (\App\Models\KeyValue::where('key', 'reinvest_percent')->value('value') / 100))) * 100 }}%"
                );
            });
        </script>
    @endif
    @if (Auth::user()->type == 'member')
        <script src="//d3js.org/d3.v3.min.js"></script>
        <script src="{{ asset('tree/script.js') }}"></script>
        <script>
            function loadD3Data(phase) {
                d3.json("{{ url('hirearchy/' . Auth::user()->username) }}/" + phase, function(error,
                    flare) {
                    if (error) throw error;

                    root = flare;
                    root.x0 = height / 2;
                    root.y0 = 0;

                    function collapse(d) {
                        if (d.children) {
                            d._children = d.children;
                            d._children.forEach(collapse);
                            d.children = null;
                        }
                    }

                    root.children.forEach(collapse);
                    update(root);
                });

            }
            loadD3Data('User Q');

            function phase(phase) {
                $('.phase').html('Basic Mitra Q');
                // $('.phase').html('Peringkat ' + phase + ' ');
                loadD3Data(phase);
            }
        </script>
    @endif
@endsection
