@extends('shop.layout.app')
@section('title', 'Checkout')
@section('style')
    <link href="{{ asset('inspinia/css/plugins/chosen/bootstrap-chosen.css') }}" rel="stylesheet">
    <style>
        a {
            outline: none !important;
        }
    </style>
@endsection
@section('content')
    <div class="container clearfix">

        <div class="fancy-title title-border mb-4 title-center">
            <h4>Keranjang Belanja</h4>
        </div>

        <form id="buy" action="{{ url('transaction') }}" method="POST" onsubmit="checkout.disabled = true;">
            @csrf
            <input type="hidden" id="courier_text" name="courier_text">
            <div class="row">
                <div class="col-md-9 mb-4">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Alamat Pengiriman</h5>
                        </div>
                        @if (Auth::user())
                            <div class="ibox-content">
                                <div class="mb-3">
                                    <select class="form-control sm-form-control" name="address_id" id="address_id" required>
                                        <option selected value="{{ Auth::user()->address->id }}">
                                            {{ Auth::user()->address->name }}</option>
                                        @php
                                            $dropship = Auth::user()
                                                ->addresses()
                                                ->where('name', 'Dropship')
                                                ->first();
                                        @endphp
                                        @if ($dropship)
                                            <option value="{{ $dropship->id }}">{{ $dropship->name }}</option>
                                        @else
                                            <option value="new">Dropship</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            @if (false)
                                <div class="ibox-content" id="main_address">
                                    @if (Auth::user()->address)
                                        <h5>{{ Auth::user()->address->name }}</h5>
                                        <p>{{ Auth::user()->address->address }}</p>
                                        <p>Provinsi
                                            <b>{{ Auth::user()->address->subdistrict->city->province->province }}</b><br />
                                            Kabupaten/Kota
                                            <b>{{ Auth::user()->address->subdistrict->city->city_name }}</b><br />
                                            Kecamatan <b>{{ Auth::user()->address->subdistrict->subdistrict_name }}</b>
                                        </p>
                                    @else
                                        <h5><a href="{{ url('account') }}"><u>Tambah Alamat</u></a></h5>
                                    @endif
                                </div>
                            @endif
                        @endif
                        <div class="ibox-content" id="dropship_address">
                            <div class="row mb-3">
                                <label class="nott col-sm-3 col-form-label">Nama Alamat</label>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control sm-form-control"
                                        {{ Auth::user() ? 'readonly value=Dropship' : '' }} required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="nott col-sm-3 col-form-label">Nama Penerima</label>
                                <div class="col-sm-9">
                                    <input type="text" name="recipient" class="form-control sm-form-control" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="nott col-sm-3 col-form-label">Email</label>
                                <div class="col-sm-9">
                                    <input type="email" name="email" class="form-control sm-form-control" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="nott col-sm-3 col-form-label">Nomor HP</label>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <span class="input-group-text sm-form-control">+62</span>
                                        <input type="text" name="phone" class="form-control sm-form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="nott col-sm-3 col-form-label">Alamat</label>
                                <div class="col-md-9">
                                    <textarea class="form-control sm-form-control" name="address" rows="5" style="resize: vertical;" required></textarea>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="nott col-sm-3 col-form-label">Provinsi</label>
                                <div class="col-md-9">
                                    <select id="province" class="form-control sm-form-control" name="province_id"
                                        tabindex="2" required>
                                        <option selected disabled>Pilih Provinsi</option>
                                        @foreach ($provinces as $a)
                                            <option value="{{ $a->province_id }}">
                                                {{ $a->province }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="nott col-sm-3 col-form-label">Kabupaten/Kota</label>
                                <div class="col-md-9">
                                    <select id="city" class="form-control sm-form-control" name="city_id"
                                        tabindex="2" required>
                                        <option selected disabled>Pilih Kabupaten/Kota</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="nott col-sm-3 col-form-label">Kecamatan</label>
                                <div class="col-md-9">
                                    <select id="subdistrict" class="form-control sm-form-control" name="subdistrict_id"
                                        tabindex="2" required>
                                        <option selected disabled>Pilih Kecamatan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="nott col-sm-3 col-form-label">Kode Pos</label>
                                <div class="col-sm-9">
                                    <input type="text" name="postal_code" class="form-control sm-form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>({{ count($carts) }}) Produk</h5>
                        </div>
                        @foreach ($carts as $a)
                            <div class="ibox-content">
                                <div class="table-responsive">
                                    <table class="table shoping-cart-table">
                                        <tbody>
                                            <tr>
                                                <td class="align-middle" width="90">
                                                    <img class="img-cart" src="{{ asset($a->product->image_path) }}" />
                                                </td>
                                                <td class="desc align-middle">
                                                    <h5>
                                                        <a href="{{ url('product/' . $a->product->dash_name) }}"
                                                            class="text-navy">
                                                            {{ $a->product->name }}
                                                        </a>
                                                    </h5>
                                                    <p class="small mb-1">
                                                        {!! $a->product->content ? str_limit($a->product->content) : 'Tidak ada keterangan produk' !!}
                                                    </p>
                                                </td>

                                                <td class="align-middle text-end">
                                                    Rp&nbsp;{{ number_format($a->price, 0, ',', '.') }}
                                                    <!-- <s class="small text-muted">$230,00</s> -->
                                                </td>
                                                <td class="align-middle" width="65">
                                                    <input type="text" name="qty"
                                                        class="form-control sm-form-control" value="{{ $a->qty }}"
                                                        required disabled style="min-width: 50px; text-align: center;" />
                                                </td>
                                                <td class="align-middle text-end">
                                                    <h4 class="mb-0">
                                                        Rp&nbsp;{{ number_format($a->price_total, 0, ',', '.') }}
                                                    </h4>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Master Stokis</h5>
                        </div>
                        <div class="ibox-content">
                            <div class="mb-3">
                                <select class="form-control sm-form-control" name="master_stockist_id"
                                    id="master_stockist_id" required>
                                    <option selected value="{{ \App\Models\User::where('type', 'admin')->value('id') }}">
                                        Perusahaan</option>
                                    @if (Auth::guest() || (Auth::user() && !Auth::user()->is_master_stockist))
                                        @foreach ($stockists as $a)
                                            <option value="{{ $a->id }}">
                                                {{ $a->name . ' (' . $a->username . ')' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Kurir</h5>
                        </div>
                        <div class="ibox-content">
                            <div class="mb-3">
                                <select id="courier_cost" required name="courier_cost"
                                    class="form-control sm-form-control">
                                    <option selected disabled>Pilih Kurir</option>
                                    <option value="0">COD</option>
                                    <option value="0">Ambil di Kantor</option>
                                    @if (isset($response))
                                        @foreach ($response->rajaongkir->results as $a)
                                            @foreach ($a->costs as $b)
                                                <option value="{{ $b->cost[0]->value }}">{{ $a->name }}
                                                    {{ $b->service }} Rp
                                                    {{ number_format($b->cost[0]->value, 0, ',', '.') }}
                                                    {{ $b->cost[0]->etd ? str_replace(' HARI', '', '(' . $b->cost[0]->etd) . ' hari)' : '' }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Pembayaran</h5>
                        </div>
                        <div class="ibox-content">
                            <p>Silahkan transfer <span id="total"></span> ke rekening
                                {{ \App\Models\User::where('type', 'admin')->first()->bank->name }}
                                <b>{{ \App\Models\User::where('type', 'admin')->first()->bank_account }}</b> a/n
                                {{ \App\Models\User::where('type', 'admin')->first()->bank_as }}
                                @if (false)
                                    atau
                                    {{ \App\Models\Bank::where('name', 'BANK BCA')->first()->name }}
                                    <b>0376 155 166</b> a/n
                                    {{ \App\Models\User::where('type', 'admin')->first()->bank_as }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Ringkasan Belanja</h5>
                        </div>
                        <div class="ibox-content">
                            <span>
                                Total
                            </span>
                            <h2 class="font-bold total">
                                Rp&nbsp;{{ number_format($carts->sum('price_total'), 0, ',', '.') }}
                            </h2>

                            <hr />
                            <span class="text-muted small">
                                *Tidak termasuk biaya pengiriman
                            </span>
                            <div class="m-t-sm mt-1">
                                <div class="btn-group">
                                    @if (Auth::user())
                                        <button type="submit" class="button nott fw-normal ms-1 my-0"
                                            style="padding: 5px 10px; font-size: 12px" name="checkout"><i
                                                class="fa fa-shopping-cart"></i>
                                            Checkout</button>
                                    @else
                                        <button type="button" class="button nott fw-normal ms-1 my-0 checkout"
                                            style="padding: 5px 10px; font-size: 12px"><i class="fa fa-shopping-cart"></i>
                                            Checkout</button>
                                    @endif
                                    <a href="javascript:history.back()" class="button nott fw-normal ms-1 my-0"
                                        style="padding: 5px 10px; font-size: 12px"> Kembali</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Support</h5>
                        </div>
                        <div class="ibox-content text-center">
                            <h3><i class="fa fa-phone"></i> +62 {{ App\Models\ContactUs::first()->phone }}</h3>
                            <span class="small">
                                Silahkan kontak dengan kami jika Anda memiliki pertanyaan. Kami tersedia 24 jam.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
@endsection
@section('script')
    <!-- Chosen -->
    <script src="{{ asset('inspinia/js/plugins/chosen/chosen.jquery.js') }}"></script>
    @if (Auth::guest())
        <script>
            var carts = localStorage.getItem('carts') ? JSON.parse(localStorage.getItem('carts')) : [0];
        </script>
    @else
        <script>
            var carts = {{ $carts->pluck('id') }};
        </script>
    @endif
    <script>
        function findCourier(subdistrict_id) {
            $('#courier_cost').html(
                '<option selected disabled>Pilih Kurir</option><option value="0">COD</option><option value="0">Ambil di Kantor</option>'
                );
            var input = {
                _token: '{{ csrf_token() }}',
                subdistrict_id: subdistrict_id,
                master_stockist_id: $("#master_stockist_id").find(":selected").val(),
                carts: carts,
            };
            $.post('{{ url('courier') }}', input, function(data) {
                data = JSON.parse(data);
                $.each(data.rajaongkir.results, function(i, result) {
                    $.each(result.costs, function(j, cost) {
                        $('#courier_cost').append($('<option>').text(result.name + ' ' + cost
                                .service + ' ' + cost.cost[0].value + ' ' + cost.cost[0].etd)
                            .attr('value', cost.cost[0].value));
                    });
                });
            });
        }
        $(document).ready(function() {
            $('#province').change(function() {
                $.get('{{ url('city') }}/' + this.value, function(data) {
                    $('#city').html('<option selected disabled>Pilih Kabupaten/Kota</option>');
                    $.each(data, function(i, value) {
                        $('#city').append($('<option>').text(value.type + ' ' + value
                            .city_name).attr('value', value.city_id));
                    });
                    $('#subdistrict').html('<option selected disabled>Pilih Kecamatan</option>');
                });
            });
            $('#city').change(function() {
                $.get('{{ url('subdistrict') }}/' + this.value, function(data) {
                    $('#subdistrict').html('<option selected disabled>Pilih Kecamatan</option>');
                    $.each(data, function(i, value) {
                        $('#subdistrict').append($('<option>').text(value.subdistrict_name)
                            .attr('value', value.subdistrict_id));
                    });
                });
            });
            $('#subdistrict').change(function() {
                findCourier(this.value);
            });
            $('#master_stockist_id').change(function() {
                if ($('#subdistrict').val()) {
                    findCourier($('#subdistrict').val());
                }
            });
        });
    </script>
    @if (Auth::guest())
        <script type="text/javascript">
            $(document).ready(function() {
                $('.checkout').click(function() {
                    $(this).prop('disabled', true);
                    // check form validation
                    if (!$("input[name=name]").val() ||
                        !$("input[name=recipient]").val() ||
                        !$("input[name=email]").val() ||
                        !$("input[name=phone]").val() ||
                        !$("textarea[name=address]").val() ||
                        $("select[name=province_id] option:selected").val() == 'Pilih Provinsi' ||
                        $("select[name=city_id] option:selected").val() == 'Pilih Kabupaten/Kota' ||
                        $("select[name=subdistrict_id] option:selected").val() == 'Pilih Kecamatan' ||
                        !$("input[name=postal_code]").val()
                    ) {
                        toastr.error('Silahkan lengkapi alamat terlebih dahulu', 'Error');
                        $(this).prop('disabled', false);
                        return 0;
                    } else if ($('#courier_cost option:selected').val() == 'Pilih Kurir') {
                        toastr.error('Silahkan pilih kurir terlebih dahulu', 'Error');
                        $(this).prop('disabled', false);
                        return 0;
                    } else if ($('#buy').validate()) {
                        // save address
                        var inputAddress = {
                            _token: '{{ csrf_token() }}',
                            name: $("input[name=name]").val(),
                            recipient: $("input[name=recipient]").val(),
                            email: $("input[name=email]").val(),
                            phone: $("input[name=phone]").val(),
                            address: $("textarea[name=address]").val(),
                            province_id: $("select[name=province_id] option:selected").val(),
                            city_id: $("select[name=city_id] option:selected").val(),
                            subdistrict_id: $("select[name=subdistrict_id] option:selected").val(),
                            postal_code: $("input[name=postal_code]").val(),
                            carts: localStorage.getItem('carts') ? JSON.parse(localStorage.getItem(
                                'carts')) : [],
                            address_id: localStorage.getItem('address_id'),
                        };
                        $.post('{{ url('address') }}', inputAddress, function(address_id) {
                            localStorage.setItem('address_id', address_id);
                            var transactions = localStorage.getItem('transactions') ? JSON.parse(
                                localStorage.getItem('transactions')) : [];
                            var inputTransaction = {
                                _token: '{{ csrf_token() }}',
                                address_id: localStorage.getItem('address_id'),
                                shipment: $('#courier_cost option:selected').text(),
                                shipment_fee: $('#courier_cost option:selected').val(),
                                carts: localStorage.getItem('carts') ? JSON.parse(localStorage
                                    .getItem('carts')) : [],
                                sponsor: "{{ isset($_COOKIE['sponsor']) ? $_COOKIE['sponsor'] : '' }}",
                                master_stockist_id: $('#master_stockist_id option:selected').val(),
                            }
                            $.post('{{ url('transaction') }}', inputTransaction, function(
                                transaction_id) {
                                if (transaction_id) {
                                    transactions.push(transaction_id);
                                    localStorage.setItem('transactions', JSON.stringify(
                                        transactions));
                                }
                                window.location.href = '/transaction?transactions=' + JSON
                                    .stringify(transactions);
                            });
                        });
                    }
                });
                if (localStorage.getItem('address_id')) {
                    $.get('/address/' + localStorage.getItem('address_id'), function(address) {
                        $("input[name=name]").val(address.name);
                        $("input[name=recipient]").val(address.recipient);
                        $("input[name=email]").val(address.email);
                        $("input[name=phone]").val(address.phone);
                        $("textarea[name=address]").html(address.address);
                        $("input[name=postal_code]").val(address.postal_code);
                        $.get('/province', function(data) {
                            $('#province').html('');
                            $.each(data, function(i, value) {
                                var option = $('<option>').text(value.province).attr('value',
                                    value.province_id);
                                if (value.province_id == address.province_id) {
                                    option.attr('selected', 'selected');
                                }
                                $('#province').append(option);
                            });
                        });
                        $.get('/city/' + address.province_id, function(data) {
                            $('#city').html('');
                            $.each(data, function(i, value) {
                                var option = $('<option>').text(value.type + ' ' + value
                                    .city_name).attr('value', value.city_id);
                                if (value.city_id == address.city_id) {
                                    option.attr('selected', 'selected');
                                }
                                $('#city').append(option);
                            });
                        });
                        $.get('/subdistrict/' + address.city_id, function(data) {
                            $('#subdistrict').html('');
                            $.each(data, function(i, value) {
                                var option = $('<option>').text(value.subdistrict_name).attr(
                                    'value', value.subdistrict_id);
                                if (value.subdistrict_id == address.subdistrict_id) {
                                    option.attr('selected', 'selected');
                                    findCourier(address.subdistrict_id);
                                }
                                $('#subdistrict').append(option);
                            });
                        });
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        if (textStatus == 'timeout') {
                            console.log('The server is not responding');
                        }
                        if (textStatus == 'error') {
                            console.log(errorThrown);
                            if (errorThrown == 'Not Found') {
                                localStorage.removeItem('address_id');
                            }
                        }
                    });
                }
            });
        </script>
    @else
        <script>
            function getAddress(address_id) {
                $.get('/address/' + address_id, function(address) {
                    $("input[name=name]").val(address.name);
                    $("input[name=recipient]").val(address.recipient);
                    $("input[name=email]").val(address.email);
                    $("input[name=phone]").val(address.phone);
                    $("textarea[name=address]").html(address.address);
                    $("input[name=postal_code]").val(address.postal_code);
                    $.get('/province', function(data) {
                        $('#province').html('<option disabled>Pilih Provinsi</option>');
                        $.each(data, function(i, value) {
                            var option = $('<option>').text(value.province).attr('value', value
                                .province_id);
                            if (value.province_id == address.province_id) {
                                option.attr('selected', 'selected');
                            }
                            $('#province').append(option);
                        });
                    });
                    $.get('/city/' + address.province_id, function(data) {
                        $('#city').html('<option selected disabled>Pilih Kabupaten/Kota</option>');
                        $.each(data, function(i, value) {
                            var option = $('<option>').text(value.type + ' ' + value.city_name).attr(
                                'value', value.city_id);
                            if (value.city_id == address.city_id) {
                                option.attr('selected', 'selected');
                            }
                            $('#city').append(option);
                        });
                    });
                    $.get('/subdistrict/' + address.city_id, function(data) {
                        $('#subdistrict').html('<option selected disabled>Pilih Kecamatan</option>');
                        $.each(data, function(i, value) {
                            var option = $('<option>').text(value.subdistrict_name).attr('value', value
                                .subdistrict_id);
                            if (value.subdistrict_id == address.subdistrict_id) {
                                option.attr('selected', 'selected');
                                findCourier(address.subdistrict_id);
                            }
                            $('#subdistrict').append(option);
                        });
                    });
                });
            }
            $(document).ready(function() {
                getAddress($('#address_id').val());
                $('#address_id').change(function(event) {
                    if ($(this).val() != 'new') {
                        getAddress($(this).val());
                    } else {
                        $('#courier_cost').html(
                            '<option selected disabled>Pilih Kurir</option><option value="0">COD</option><option value="0">Ambil di Kantor</option>'
                            );
                        $("input[name=name]").val('Dropship');
                        $("input[name=recipient]").val('');
                        $("input[name=email]").val('');
                        $("input[name=phone]").val('');
                        $("textarea[name=address]").html('');
                        $("input[name=postal_code]").val('');
                        $('#subdistrict').html('<option selected disabled>Pilih Kecamatan</option>');
                        $('#city').html('<option selected disabled>Pilih Kabupaten/Kota</option>');
                        $("#province option:first").attr('selected', true);
                    }
                });
            });
        </script>
    @endif
    <script>
        $(document).ready(function() {
            $('#courier_cost').change(function(event) {
                $('#total').html('sejumlah <b>Rp ' + (parseInt($('#courier_cost').val()) +
                    {{ $carts->sum('price_total') }}).toLocaleString('id') + '</b>');
                $('#courier_text').val($('#courier_cost option:selected').text());
                $('.total').html('Rp ' + (parseInt($('#courier_cost').val()) +
                    {{ $carts->sum('price_total') }}).toLocaleString('id'))
            });
        });
    </script>
@endsection
