@extends('marketplace.layouts.inspinia')
@section('title')
    Buy
@endsection
@section('style')
    <link href="{{ asset('inspinia/css/plugins/chosen/bootstrap-chosen.css') }}" rel="stylesheet">
    <style>
        a {
            outline: none !important;
        }
    </style>
@endsection
@section('content')
    <section id="products" class="container services animated fadeInDown">
        <form id="buy" action="{{ url('transaction') }}" method="POST">
            @csrf
            <input type="hidden" id="courier_text" name="courier_text">
            <div class="row">
                <div class="col-md-9">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Shipping Address</h5>
                        </div>
                        <div class="ibox-content">
                            @if (Auth::user())
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
                                    <h5><a href="{{ url('account') }}"><u>Add address</u></a></h5>
                                @endif
                            @else
                                <div class="form-horizontal">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Email</label>
                                        <div class="col-sm-10">
                                            <input type="email" name="email" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Name</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Phone Number</label>
                                        <div class="col-md-10">
                                            <div class="input-group">
                                                <span class="input-group-addon">+62</span>
                                                <input type="text" name="phone" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Address</label>
                                        <div class="col-md-10">
                                            <textarea class="form-control" name="address" rows="5" style="resize: vertical;" required></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Province</label>
                                        <div class="col-md-10">
                                            <select id="province" class="form-control" name="province_id" tabindex="2"
                                                required>
                                                <option selected disabled>Select a Province</option>
                                                @foreach ($provinces as $a)
                                                    <option value="{{ $a->province_id }}">
                                                        {{ $a->province }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">City</label>
                                        <div class="col-md-10">
                                            <select id="city" class="form-control" name="city_id" tabindex="2"
                                                required>
                                                <option selected disabled>Select a City</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">Subdistrict</label>
                                        <div class="col-md-10">
                                            <select id="subdistrict" class="form-control" name="subdistrict_id"
                                                tabindex="2" required>
                                                <option selected disabled>Select a Subdistrict</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Postal Code</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="postal_code" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="ibox">
                        <div class="ibox-title">
                            <span class="pull-right">(<strong>{{ count($carts) }}</strong>) items</span>
                            <h5>Items</h5>
                        </div>
                        @foreach ($carts as $a)
                            <div class="ibox-content">
                                <div class="table-responsive">
                                    <table class="table shoping-cart-table">
                                        <tbody>
                                            <tr>
                                                <td width="90">
                                                    <img class="img-cart" src="{{ asset($a->product->image_path) }}" />
                                                </td>
                                                <td class="desc">
                                                    <h3>
                                                        <a href="{{ url('product/' . $a->product->dash_name) }}"
                                                            class="text-navy">
                                                            {{ $a->product->name }}
                                                        </a>
                                                    </h3>
                                                    <p class="small">
                                                        {!! str_limit($a->product->content) !!}
                                                    </p>
                                                </td>

                                                <td>
                                                    Rp&nbsp;{{ number_format($a->price) }}
                                                    <!-- <s class="small text-muted">$230,00</s> -->
                                                </td>
                                                <td width="65">
                                                    <input type="text" name="qty" class="form-control"
                                                        value="{{ $a->qty }}" required disabled />
                                                </td>
                                                <td>
                                                    <h4>
                                                        Rp&nbsp;{{ number_format($a->price_total) }}
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
                            <h5>Courier</h5>
                        </div>
                        <div class="ibox-content">
                            <div class="form-group">
                                <select id="courier_cost" required name="courier_cost" class="form-control">
                                    <option selected disabled>Select courier</option>
                                    @if (isset($response))
                                        @foreach ($response->rajaongkir->results as $a)
                                            @foreach ($a->costs as $b)
                                                <option value="{{ $b->cost[0]->value }}">{{ $a->name }}
                                                    {{ $b->service }} Rp
                                                    {{ number_format($b->cost[0]->value) }}
                                                    {{ $b->cost[0]->etd ? str_replace(' HARI', '', '(' . $b->cost[0]->etd) . ' hari)' : '' }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Payment</h5>
                        </div>
                        <div class="ibox-content">
                            <p>Silahkan transfer <span id="total"></span> ke rekening
                                {{ \App\Models\User::where('type', 'admin')->first()->bank->name }}
                                <b>{{ \App\Models\User::where('type', 'admin')->first()->bank_account }}</b>
                                atau
                                {{ \App\Models\Bank::where('name', 'BANK BCA')->first()->name }}
                                <b>0376 155 166</b> a/n
                                {{ \App\Models\User::where('type', 'admin')->first()->bank_as }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Cart Summary</h5>
                        </div>
                        <div class="ibox-content">
                            <span>
                                Total
                            </span>
                            <h2 class="font-bold total">
                                Rp&nbsp;{{ number_format($carts->sum('price_total')) }}
                            </h2>

                            <hr />
                            <span class="text-muted small">
                                *Exclude shipping cost
                            </span>
                            <div class="m-t-sm">
                                <div class="btn-group">
                                    @if (Auth::user())
                                        <button type="submit" class="btn btn-primary btn-sm"
                                            style="padding: 5px 10px; font-size: 12px"><i class="fa fa-shopping-cart"></i>
                                            Checkout</button>
                                    @else
                                        <button type="button" class="btn btn-primary btn-sm checkout"
                                            style="padding: 5px 10px; font-size: 12px"><i class="fa fa-shopping-cart"></i>
                                            Checkout</button>
                                    @endif
                                    <a href="#" class="btn btn-white btn-sm"> Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>Support</h5>
                        </div>
                        <div class="ibox-content text-center">
                            <h3><i class="fa fa-phone"></i> +62 {{ App\Models\ContactUs::first()->phone }}</h3>
                            <span class="small">
                                Please contact with us if you have any questions. We are avalible 24h.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection
@section('script')
    <!-- Chosen -->
    <script src="{{ asset('inspinia/js/plugins/chosen/chosen.jquery.js') }}"></script>
    @if (Auth::guest())
        <script type="text/javascript">
            function findCourier(subdistrict_id) {
                var input = {
                    _token: '{{ csrf_token() }}',
                    subdistrict_id: subdistrict_id,
                    carts: localStorage.getItem('carts') ? JSON.parse(localStorage.getItem('carts')) : [],
                };
                $.post('{{ url('courier') }}', input, function(data) {
                    $('#courier_cost').html('<option selected disabled>Select courier</option>');
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
                        $('#city').html('<option selected disabled>Select a City</option>');
                        $.each(data, function(i, value) {
                            $('#city').append($('<option>').text(value.type + ' ' + value
                                .city_name).attr('value', value.city_id));
                        });
                        $('#subdistrict').html(
                            '<option selected disabled>Select a Subdistrict</option>');
                    });
                });
                $('#city').change(function() {
                    $.get('{{ url('subdistrict') }}/' + this.value, function(data) {
                        $('#subdistrict').html(
                            '<option selected disabled>Select a Subdistrict</option>');
                        $.each(data, function(i, value) {
                            $('#subdistrict').append($('<option>').text(value.subdistrict_name)
                                .attr('value', value.subdistrict_id));
                        });
                    });
                });
                $('#subdistrict').change(function() {
                    findCourier(this.value);
                });
                $('.checkout').click(function() {
                    // check form validation
                    if (
                        !$("input[name=email]").val() ||
                        !$("input[name=name]").val() ||
                        !$("input[name=phone]").val() ||
                        !$("textarea[name=address]").val() ||
                        $("select[name=province_id] option:selected").val() == 'Select a Province' ||
                        $("select[name=city_id] option:selected").val() == 'Select a City' ||
                        $("select[name=subdistrict_id] option:selected").val() == 'Select a Subdistrict' ||
                        !$("input[name=postal_code]").val() ||
                        $('#courier_cost option:selected').val() == 'Select courier'
                    ) {
                        toastr.error('Mohon lengkapi alamat dan pilih kurir', 'Error');
                        return 0;
                    }
                    // save address
                    var inputAddress = {
                        _token: '{{ csrf_token() }}',
                        email: $("input[name=email]").val(),
                        name: $("input[name=name]").val(),
                        phone: $("input[name=phone]").val(),
                        address: $("textarea[name=address]").val(),
                        province_id: $("select[name=province_id] option:selected").val(),
                        city_id: $("select[name=city_id] option:selected").val(),
                        subdistrict_id: $("select[name=subdistrict_id] option:selected").val(),
                        postal_code: $("input[name=postal_code]").val(),
                        carts: localStorage.getItem('carts') ? JSON.parse(localStorage.getItem('carts')) :
                        [],
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
                            carts: localStorage.getItem('carts') ? JSON.parse(localStorage.getItem(
                                'carts')) : [],
                            sponsor: "{{ isset($_COOKIE['sponsor']) ? $_COOKIE['sponsor'] : '' }}"
                        }
                        $.post('{{ url('transaction') }}', inputTransaction, function(transaction_id) {
                            console.log(transaction_id)
                            if (transaction_id) {
                                transactions.push(transaction_id);
                                localStorage.setItem('transactions', JSON.stringify(
                                    transactions));
                            }
                            window.location.href = '/transaction?transactions=' + JSON
                                .stringify(transactions);
                        });
                    });
                });
                if (localStorage.getItem('address_id')) {
                    $.get('/address/' + localStorage.getItem('address_id'), function(address) {
                        $("input[name=email]").val(address.email);
                        $("input[name=name]").val(address.name);
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
    @endif
    <script>
        $(document).ready(function() {
            $('#courier_cost').change(function(event) {
                $('#total').html('sejumlah <b>Rp ' + (parseInt($('#courier_cost').val()) +
                    {{ $carts->sum('price_total') }}).toLocaleString('us') + '</b>');
                $('#courier_text').val($('#courier_cost option:selected').text());
                $('.total').html('Rp ' + (parseInt($('#courier_cost').val()) +
                    {{ $carts->sum('price_total') }}).toLocaleString('us'))
            });
        });
    </script>
@endsection
