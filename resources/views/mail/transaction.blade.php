Halo <strong>{{ isset($user) ? $user->name : $address->name }}</strong>,
<p>
    Detail transaksi Anda sebagai berikut <br> <br>
    ID <strong>{{ $transaction->created_at->format('YmdHis') }}</strong> <br>
    Link <strong><a
            href="{{ url('transaction?code=' . $transaction->created_at->format('YmdHis')) }}">{{ url('transaction?code=' . $transaction->created_at->format('YmdHis')) }}</a></strong>
    <br>
    Jasa pengiriman <strong>{{ $transaction->shipment }}</strong> <br>
    Total pembayaran <strong>Rp {{ number_format($transaction->price_total) }}</strong> <br>

<p>Silahkan transfer sejumlah
    <b>Rp&nbsp;{{ number_format($transaction->price_total) }}</b> ke
    rekening
    {{ \App\Models\User::where('type', 'admin')->first()->bank->name }}
    <b>{{ \App\Models\User::where('type', 'admin')->first()->bank_account }}</b>
</p>
