<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Auth;
use Session;
use Response;
use App\Models\User;

class CartController extends Controller
{
    public function __construct()
    {
        $this->key = '2269f77837513d8cd5bc7677f48c9234';
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->carts) {
            $carts = Cart::whereIn('id', json_decode(request()->carts))->whereNull('transaction_id')->latest()->get();
        } else if (Auth::guest()) {
            $carts = Cart::whereNull('id')->latest()->get();
        } else {
            $carts = Auth::user()->carts()->whereNull('transaction_id')->latest()->get();
        }
        return view('shop.cart', compact('carts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (request()->ajax()) {
            $carts = $request->carts ?? [];
            $cart = Cart::whereIn('id', $carts)->whereNull('transaction_id')->where('product_id', $request->product_id)->first();
            if ($cart) {
                $cart->increment('qty');
                return null;
            } else {
                $cart = Cart::create(
                    array(
                        'product_id' => $request->product_id,
                        'qty' => 1
                    )
                );
                return $cart->id;
            }
        }
        // check cradmin
        if (Auth::user()->type == 'cradmin') {
            Session::flash('fail', 'Silahkan keluar dari akun Admin CR');
            return back();
        }
        // check if cart exists
        $cart = Auth::user()->carts()->whereNull('transaction_id')->where('product_id', $request->product_id)->first();
        if ($cart) {
            $cart->increment('qty');
        } else {
            Auth::user()->carts()->create(
                array(
                    'product_id' => $request->product_id,
                    'qty' => 1
                )
            );
        }
        return redirect('cart');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function show(Cart $cart)
    {
        return Cart::where('id', $cart->id)->with('product')->first()->toJson();
        // return Response::json(Cart::find($cart->id)->with('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function edit(Cart $cart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cart $cart)
    {
        $cart->update($request->all());
        Session::flash('success', 'Updated');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cart $cart)
    {
        $cart->delete();
        Session::flash('success', 'Deleted');
        return back();
    }

    public function buy()
    {
        if (request()->carts) {
            $carts = Cart::whereIn('id', json_decode(request()->carts))->whereNull('transaction_id')->latest()->get();
        } else {
            if (Auth::guest()) {
                Session::flash('fail', 'Cart belanja anda kosong');
                return redirect('cart');
            }
            if (!Auth::user()->address) {
                Session::flash('fail', 'Silahkan lengkapi alamat Anda');
                return redirect('account');
            } else if (!Auth::user()->address->subdistrict_id) {
                Session::flash('fail', 'Silahkan lengkapi alamat Anda');
                return redirect('account');
            }
            $carts = Auth::user()->carts()->whereNull('transaction_id')->latest()->get();
        }
        foreach ($carts as $cart) {
            $cart->update([
                'name' => $cart->product->name,
                'price' => $cart->product->price_used,
                'price_total' => $cart->product->price_used * $cart->qty,
                'weight' => $cart->product->weight,
                'weight_total' => $cart->product->weight * $cart->qty,
                'poin' => $cart->product->poin,
                'poin_total' => $cart->product->poin * $cart->qty,
            ]);
        }
        if (count($carts)) {
            $stockists = User::where('type', 'member')->where('is_master_stockist', true);
            foreach ($carts as $cart) {
                $stockists = $stockists->whereHas('officialTransactionStockists', function ($q) use ($cart) {
                    $q->having('product_id', $cart->product_id)->groupBy('product_id')->havingRaw('sum(current) >= ' . $cart->qty);
                });
            }
            $stockists = $stockists->oldest()->get();
            $provinces = \App\Models\Province::orderBy('province')->get();
            $cities = \App\Models\City::orderBy('city_name')->get();
            $subdistricts = \App\Models\Subdistrict::orderBy('subdistrict_name')->get();
            $client = new Client();
            if (Auth::user()) {
                try {
                    $response = $client->request('POST', 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                        'headers' => [
                            'key' => $this->key,
                            'Content-Type' => 'application/x-www-form-urlencoded'
                        ],
                        'form_params' => [
                            'origin' => User::where('type', 'admin')->first()->address->subdistrict_id,
                            'destination' => Auth::user()->address->subdistrict_id,
                            'weight' => $carts->sum('weight_total'),
                            'courier' => 'jne:jnt',
                            'price' => 'lowest'
                        ]
                    ]);
                    $responseBody = json_decode($response->getBody(), true);
                    // Transform response baru ke format lama yang diharapkan view
                    $transformedResponse = $this->transformRajaOngkirResponse($responseBody);
                    // Convert ke object untuk view blade
                    $response = json_decode(json_encode($transformedResponse));
                    return view('shop.buy', compact('carts', 'provinces', 'cities', 'subdistricts', 'stockists', 'response'));
                } catch (\Exception $e) {
                    // Fallback jika API error
                    $response = (object) [
                        'rajaongkir' => (object) [
                            'results' => []
                        ]
                    ];
                    Session::flash('warning', 'Gagal mengambil data ongkos kirim. Silakan coba lagi atau hubungi admin.');
                    return view('shop.buy', compact('carts', 'provinces', 'cities', 'subdistricts', 'stockists', 'response'));
                }
            }
            return view('shop.buy', compact('carts', 'provinces', 'cities', 'subdistricts', 'stockists'));
        }
        return redirect('cart');
    }

    public function courier(Request $request)
    {
        // check master stockist
        $stockist = User::find($request->master_stockist_id);
        if (!$stockist) {
            return response()->json([
                'status' => 'error',
                'message' => 'master stockist not found',
            ], 500);
        }
        if (!$stockist->address) {
            return response()->json([
                'status' => 'error',
                'message' => 'address not found',
            ], 500);
        }
        $carts = Cart::whereIn('id', $request->carts)->whereNull('transaction_id')->latest()->get();
        $client = new Client();
        try {
            $response = $client->request('POST', 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'headers' => [
                    'key' => $this->key,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'origin' => $stockist->address->subdistrict_id,
                    'destination' => $request->subdistrict_id,
                    'weight' => $carts->sum('weight_total'),
                    'courier' => 'jne:jnt',
                    'price' => 'lowest'
                ]
            ]);
            $responseBody = json_decode($response->getBody(), true);
            // Transform response baru ke format lama yang diharapkan view
            $transformedResponse = $this->transformRajaOngkirResponse($responseBody);
            // Return sebagai JSON dengan Content-Type yang benar (jQuery akan otomatis parse)
            return response()->json($transformedResponse);
        } catch (\Exception $e) {
            return response()->json([
                'rajaongkir' => [
                    'results' => []
                ]
            ], 200);
        }
    }

    /**
     * Transform response API RajaOngkir baru ke format lama
     * Format baru: {"meta": {...}, "data": [{"name": "...", "code": "...", "service": "...", "cost": ..., "etd": "..."}]}
     * Format lama: {"rajaongkir": {"results": [{"name": "...", "code": "...", "costs": [{"service": "...", "cost": [{"value": ..., "etd": "..."}]}]}]}}
     */
    private function transformRajaOngkirResponse($responseBody)
    {
        if (!isset($responseBody['data']) || !is_array($responseBody['data'])) {
            return [
                'rajaongkir' => [
                    'results' => []
                ]
            ];
        }

        // Group by courier code
        $grouped = [];
        foreach ($responseBody['data'] as $item) {
            $code = $item['code'] ?? 'unknown';
            if (!isset($grouped[$code])) {
                $grouped[$code] = [
                    'name' => $item['name'] ?? '',
                    'code' => $code,
                    'costs' => []
                ];
            }
            
            // Format ETD: "6 day" -> "6 HARI" atau sesuai format lama
            $etd = '';
            if (isset($item['etd']) && $item['etd']) {
                $etd = str_replace(' day', ' HARI', $item['etd']);
            }
            
            $grouped[$code]['costs'][] = [
                'service' => $item['service'] ?? '',
                'description' => $item['description'] ?? '',
                'cost' => [
                    [
                        'value' => $item['cost'] ?? 0,
                        'etd' => $etd
                    ]
                ]
            ];
        }

        return [
            'rajaongkir' => [
                'results' => array_values($grouped)
            ]
        ];
    }

    public function check(Request $request)
    {
        if ($request->carts) {
            $cart = Cart::whereIn('id', json_decode($request->carts, true))->whereNull('transaction_id')->pluck('id');
            return json_encode($cart);
        }
    }
}