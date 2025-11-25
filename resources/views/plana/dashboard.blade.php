@extends('layout.app')
@section('title', 'Dashboard')
@section('style')
<link href="{{ asset('tree/style.css') }}" rel="stylesheet">
@endsection
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
		<div class="col-lg-8 col-md-8">
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
                                $userReward = Auth::user()->userRewards()->latest()->first();
                                @endphp
                                @if ($userReward)
                                @if ($userReward->reward->is_platinum)
                                CRI
                                @foreach (Auth::user()->userRewards()->whereHas('reward', function ($q) { $q->where('is_platinum', true); })->get() as $a)
                                <i class="mdi mdi-diamond text-info"></i>
                                @endforeach
                                @else
                                CRI
                                @foreach (Auth::user()->userRewards()->whereHas('reward', function ($q) { $q->where('is_platinum', false); })->get() as $a)
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
		<div class="col-lg-4 col-md-4">
			<div class="card card-inverse card-cr">
				<div class="card-body">
					<div class="d-flex">
						<div class="m-r-20 align-self-center">
							<h1 class="text-white"><i class="mdi mdi-cards"></i></h1>
						</div>
						<div style="width: calc(100% - (20px + 36px));">
							<h3 class="card-title text-truncate">Pin Aktivasi</h3>
							<h6 class="card-subtitle text-truncate">Jumlah Pin Aktivasi</h6>
						</div>
					</div>
					<div class="row">
						<div class="col-12 align-self-center">
							<h2 class="font-light text-white text-truncate">
								{{ number_format(Auth::user()->usableUserPins()->count()) }}
							</h2>
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
								{{ number_format(Auth::user()->sponsors()->count()) }}
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
							<h3 class="card-title text-truncate">Bonus Pasangan</h3>
							<h6 class="card-subtitle text-truncate">Bonus Pasangan Terbayar</h6>
						</div>
					</div>
					<div class="row">
						<div class="col-12 align-self-center">
							<h2 class="font-light text-white text-truncate">
								{{ number_format(Auth::user()->bonuses()->where('type', 'Komisi Pasangan')->sum('amount')) }}
							</h2>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	@if(Auth::user()->type != 'admin')
	<div class="card">
		<div class="card-body">
			<h3 class="card-title">
				Halaman Depan
				<a class="text-info float-right" onclick="copy1()" style="cursor: pointer;"><i class="mdi mdi-content-copy"></i></a>
			</h3>
			<input id="referral-1" type="hidden" value="{{ url('/?sponsor='.Auth::user()->username) }}" />
			<code style="white-space: unset;">{{ url('/?sponsor='.Auth::user()->username) }}</code>
			<hr />
			<h3 class="card-title">
				Halaman Belanja
				<a class="text-info float-right" onclick="copy2()" style="cursor: pointer;"><i class="mdi mdi-content-copy"></i></a>
			</h3>
			<input id="referral-2" type="hidden" value="{{ url('product/?sponsor='.Auth::user()->username) }}" />
			<code style="white-space: unset;">{{ url('product/?sponsor='.Auth::user()->username) }}</code>
			<hr />
			<h3 class="card-title">
				Halaman Registrasi
				<a class="text-info float-right" onclick="copy3()" style="cursor: pointer;"><i class="mdi mdi-content-copy"></i></a>
			</h3>
			<input id="referral-3" type="hidden" value="{{ url('register/?sponsor='.Auth::user()->username) }}" />
			<code style="white-space: unset;">{{ url('register/?sponsor='.Auth::user()->username) }}</code>
		</div>
	</div>
	@endif
</div>
@endsection
@section('script')
@if(Auth::user()->type != 'admin')
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
@endsection
