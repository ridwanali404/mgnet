<li class="{{ $is_left ? '' : 'timeline-inverted' }}">
    <div class="timeline-badge {{ $is_left ? 'success' : 'danger' }}"><i
            class="mdi mdi-arrow-{{ $is_left ? 'left' : 'right' }}"></i></div>
    <div class="timeline-panel">
        <div class="timeline-heading">
            <h4 class="timeline-title">{{ number_format($dailyPoin->pr) }} Poin</h4>
            <p><small class="text-muted"><i class="fa fa-clock-o"></i>{{ $dailyPoin->created_at }}</small> </p>
        </div>
        @if (isset($dailyPoin->pp))
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
                    sejumlah {{ $dailyPoin->pr }} poin.</p>
                <ol>
                    @if ($dailyPoin->is_before)
                        <li><a
                                href="{{ url('user/' . $dailyPoin->user->id . '/profile') }}">{{ $dailyPoin->user->username }}</a>
                            sisa sebelumnya {{ $pr_before_user->pr_current }} poin</li>
                    @endif
                    @if ($userPin)
                        <li><a
                                href="{{ url('user/' . $userPin->user->id . '/profile') }}">{{ $userPin->user->username }}</a>
                            {{ $userPin->name }} {{ $userPin->pin->poin_pair }} poin (pribadi)</li>
                    @endif
                    @php
                        $dailyPRSponsors = $dailyPoin->user->dailyPRSponsors($date)->get();
                    @endphp
                    @foreach ($dailyPRSponsors as $pRSponsor)
                        @php
                            $pr = $pRSponsor->userPin->pin->poin_pair;
                        @endphp
                        @if ($pr)
                            <li>
                                <a href="{{ url('user/' . $pRSponsor->id . '/profile') }}">
                                    {{ $pRSponsor->username }}
                                </a>
                                {{ $pRSponsor->userPin->name }} {{ $pr }} poin
                            </li>
                        @else
                            @php
                                $pRSponsorUserPinRO = $pRSponsor
                                    ->userPins()
                                    ->where('name', 'PIN PAKET RO')
                                    ->where('is_used', true)
                                    ->whereDate('updated_at', $date)
                                    ->get();
                            @endphp
                            @foreach ($pRSponsorUserPinRO as $a)
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
                    {{ number_format($is_left ? $pr_l : $pr_r) }}
                    poin.</p>
            </div>
        @else
            <div class="timeline-body">
                <p>Sisa pasangan dari grup <a
                        href="{{ url('user/' . $dailyPoin->user->id . '/profile') }}">{{ $dailyPoin->user->username }}</a>
                    sejumlah {{ $dailyPoin->pr }} poin.</p>
                <p>Jumlah poin {{ $is_left ? 'kiri' : 'kanan' }} saat ini
                    {{ number_format($is_left ? $pr_l : $pr_r) }}
                    poin.</p>
            </div>
        @endif
    </div>
</li>
