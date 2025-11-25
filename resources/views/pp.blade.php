<li class="{{ $is_left ? '' : 'timeline-inverted' }}">
    <div class="timeline-badge {{ $is_left ? 'success' : 'danger' }}"><i
            class="mdi mdi-arrow-{{ $is_left ? 'left' : 'right' }}"></i></div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h4 class="timeline-title">{{ number_format($dailyPoin->pp) }} Poin</h4>
            <p><small class="text-muted"><i class="fa fa-clock-o"></i>{{ $dailyPoin->created_at }}</small> </p>
        </div>
        @if (isset($dailyPoin->pr))
            @php
                $userPin = $dailyPoin->user
                    ->userPins()
                    ->whereHas('pin', function ($q_pin) {
                        $q_pin->where('poin_pair', '>', 0);
                    })
                    ->where('name', '!=', 'PIN PAKET RO')
                    ->whereDate('updated_at', $date)
                    ->first();
            @endphp
            <div class="timeline-body">
                <p>Poin pasangan dari grup <a
                        href="{{ url('user/' . $dailyPoin->user->id . '/profile') }}">{{ $dailyPoin->user->username }}</a>
                    sejumlah {{ $dailyPoin->pp }} poin.</p>
                <ol>
                    @if ($dailyPoin->is_before)
                        <li><a
                                href="{{ url('user/' . $dailyPoin->user->id . '/profile') }}">{{ $dailyPoin->user->username }}</a>
                            sisa sebelumnya {{ $pp_before_user->pp_current }} poin</li>
                    @endif
                    @if ($userPin)
                        <li><a
                                href="{{ url('user/' . $userPin->user->id . '/profile') }}">{{ $userPin->user->username }}</a>
                            {{ $userPin->name }} {{ $userPin->pin->poin_pair }} poin (pribadi)</li>
                    @endif
                    @php
                        $dailyPPSponsors = $dailyPoin->user->dailyPPSponsors($date)->get();
                    @endphp
                    @foreach ($dailyPPSponsors as $pPSponsor)
                        @php
                            $pp = $pPSponsor->userPin->pin->poin_pair;
                        @endphp
                        @if ($pp)
                            <li>
                                <a href="{{ url('user/' . $pPSponsor->id . '/profile') }}">
                                    {{ $pPSponsor->username }}
                                </a>
                                {{ $pPSponsor->userPin->name }} {{ $pp }} poin
                            </li>
                        @else
                            @php
                                $pPSponsorUserPinRO = $pPSponsor
                                    ->userPins()
                                    ->where('name', 'PIN PAKET RO')
                                    ->where('is_used', true)
                                    ->whereDate('updated_at', $date)
                                    ->get();
                            @endphp
                            @foreach ($pPSponsorUserPinRO as $a)
                                <li>
                                    <a href="{{ url('user/' . $a->user->id . '/profile') }}">
                                        {{ $a->user->username }}
                                    </a>
                                    {{ $a->name }} {{ $a->pin->poin_pair }} poin
                                </li>
                            @endforeach
                        @endif
                    @endforeach
                </ol>
                <p>Jumlah poin {{ $is_left ? 'kiri' : 'kanan' }} saat ini
                    {{ number_format($is_left ? $pp_l : $pp_r) }}
                    poin.</p>
            </div>
        @else
            <div class="timeline-body">
                <p>Sisa pasangan dari grup <a
                        href="{{ url('user/' . $dailyPoin->user->id . '/profile') }}">{{ $dailyPoin->user->username }}</a>
                    sejumlah {{ $dailyPoin->pp }} poin.</p>
                <p>Jumlah poin {{ $is_left ? 'kiri' : 'kanan' }} saat ini
                    {{ number_format($is_left ? $pp_l : $pp_r) }}
                    poin.</p>
            </div>
        @endif
    </div>
</li>
