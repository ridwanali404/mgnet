<?php

namespace App\Http\Controllers;

use App\Models\OfficialTransaction;
use App\Models\OfficialTransactionStockist;
use Illuminate\Http\Request;
use Auth;
use DateTime;
use Session;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Product;
use App\Models\ActiveWeek;

class OfficialTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // create date
        if (request()->get('month')) {
            $date = DateTime::createFromFormat('Y-m-d', request()->get('month') . '-01');
        } else {
            $date = new DateTime();
        }
        if (Auth::user()->type == 'admin') {
            $official_transactions = new OfficialTransaction;
            $official_transaction_stockists = new OfficialTransactionStockist;
        } else {
            $official_transactions = Auth::user()->officialTransactions();
            $official_transaction_stockists = Auth::user()->officialTransactionStockists();
        }
        $official_transactions = $official_transactions->whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))
            ->with(['user', 'product', 'stockist'])
            ->latest()
            ->get();
        $official_transaction_stockists = $official_transaction_stockists->whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))
            ->with(['user', 'product'])
            ->latest()
            ->get();
        return view('official-transaction', compact('official_transactions', 'official_transaction_stockists'));
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
        // check member
        // $user = Member::where('member_id', $request->member_id)->first();
        $user = User::find($request->member_id);
        if (!$user) {
            Session::flash('fail', 'Member tidak ditemukan');
            return back()->withInput();
        }
        if (Auth::user()->type == 'admin') {
            // check courier
            $product = Product::find($request->product_id);
            if ($product->is_big) {
                if (!$request->month) {
                    Session::flash('fail', 'Silahkan isi jumlah bulan pada detail Produk');
                    return back()->withInput();
                }
            }
            if (!$request->courier_cost) {
                Session::flash('fail', 'Kurir belum dipilih');
                return back()->withInput();
            }
            if ($request->courier_cost == 'Ambil di Kantor') {
                $request->request->add([
                    'shipment' => 'Ambil di Kantor',
                    'shipment_fee' => 0,
                    'code' => rand(500, 999),
                ]);
            } else if ($request->courier_cost == 'COD') {
                $request->request->add([
                    'shipment' => 'COD',
                    'shipment_fee' => 0,
                    'code' => rand(500, 999),
                ]);
            } else {
                $request->request->add([
                    'shipment' => $request->courier_text,
                    'shipment_fee' => $request->courier_cost,
                    'code' => rand(500, 999),
                ]);
            }
            $request->request->remove('courier_text');
            $request->request->remove('courier_cost');
        }
        $user_id = $user->id;
        $request->request->add([
            'user_id' => $user_id,
        ]);
        $request->request->remove('member_id');

        if (Auth::user()->is_stockist) {
            $officialTransactionStockists = Auth::user()->officialTransactionStockists()->where('product_id', $request->product_id);
            $qty = $officialTransactionStockists->sum('current');
            if ($qty < $request->qty) {
                Session::flash('fail', 'Stok tidak mencukupi');
                return back();
            }
            $request->request->add([
                'stockist_id' => Auth::id(),
                'status' => 'received',
            ]);
            // decrement stock
            $tmp_qty = $request->qty;
            foreach ($officialTransactionStockists->oldest()->get() as $a) {
                if ($a->current <= $tmp_qty) {
                    $tmp_qty = $tmp_qty - $a->current;
                    $a->decrement('current', (int) $a->current);
                    $a->increment('used', (int) $a->current);
                } else {
                    $a->decrement('current', (int) $tmp_qty);
                    $a->increment('used', (int) $tmp_qty);
                    break;
                }
            }
        }
        if ($request->is_topup) {
            $request->request->add([
                'is_topup' => true,
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now()->subMonth(),
            ]);
        }
        $product = Product::find($request->product_id);
        $price_user = $product->priceUsedByUser($user);
        $request->request->add([
            'poin' => $product->poin * $request->qty,
            'price' => $price_user * $request->qty,
            'cashback' => 0,
            'product_name' => $product->name,
            'product_price' => $price_user,
            'price_total' => ($price_user * $request->qty * ($request->month ?? 1)) + $request->code + $request->shipment_fee,
        ]);

        $officialTransaction = OfficialTransaction::create($request->all());

        $user = User::find($request->user_id);
        $sponsor = $user->sponsor;
        // cashback bonus to sponsor
        if ($user && $officialTransaction->cashback) {
            $user->bonuses()->create([
                'type' => 'Komisi Penjualan',
                'amount' => $officialTransaction->cashback,
                'description' => 'Komisi Penjualan dari belanja ' . $officialTransaction->user->username . '.',
            ]);
        }
        // Bonus Unilevel RO (premium member)
        // ro bonus is generated on closing
        if (false) {
            $i = 1;
            while ($i <= 10 && $sponsor) {
                $percent = \App\Models\KeyValue::where('key', 'monthly_ro_unilevel_' . $i)->value('value');
                if ($sponsor->userPin->price || $i == 1) {
                    $sponsor->bonuses()->create([
                        'type' => 'Bonus Unilevel RO',
                        'amount' => round($officialTransaction->poin * 1000 * $percent / 100),
                        'description' => 'Bonus Unilevel RO dari belanja ' . $officialTransaction->user->username . '.',
                    ]);
                    $i++;
                }
                $sponsor = $sponsor->sponsor;
            }
        }
        // check big transaction
        if ($product->is_big) {
            $officialTransaction->update([
                'is_big' => true,
                'month_key' => 1,
            ]);
        }
        Session::flash('success', 'Transaksi berhasil dibuat');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OfficialTransaction  $officialTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(OfficialTransaction $officialTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OfficialTransaction  $officialTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(OfficialTransaction $officialTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OfficialTransaction  $officialTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OfficialTransaction $officialTransaction)
    {
        $officialTransaction->update($request->all());
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OfficialTransaction  $officialTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(OfficialTransaction $officialTransaction)
    {
        $officialTransaction->delete();
        return back();
    }

    public function confirm(Request $request, OfficialTransaction $officialTransaction)
    {
        $officialTransaction->update([
            'status' => 'paid',
        ]);
        // check big transaction
        if ($officialTransaction->product->is_big) {
            $created_at = Carbon::parse($officialTransaction->created_at);
            for ($i = 2; $i <= $officialTransaction->month; $i++) {
                // create future transactions
                $date = $created_at->copy()->addMonthNoOverflow($i - 1);
                $officialTransaction->officialTransactions()->create([
                    'user_id' => $officialTransaction->user_id,
                    'product_id' => $officialTransaction->product_id,
                    'qty' => $officialTransaction->qty,
                    'poin' => $officialTransaction->poin,
                    'price' => $officialTransaction->price,
                    'product_name' => $officialTransaction->product_name,
                    'month_key' => $i,
                    'is_big' => true,
                    'status' => 'received',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
        // check weekly transaction
        if ($officialTransaction->product->is_weekly) {
            $week_carbon = Carbon::parse($officialTransaction->created_at);
            for ($i = 0; $i < 4; $i++) {
                $week = clone $week_carbon;
                $officialTransaction->user->activeWeeks()->create([
                    'week' => $week->addWeeks($i)->format('Y-\WW'),
                    'method' => 'official_transaction',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
        return back();
    }

    public function packed(Request $request, OfficialTransaction $officialTransaction)
    {
        $is_updated = $officialTransaction->update(
            array(
                'status' => 'packed'
            )
        );
        if ($is_updated)
            Session::flash('success', 'Updated');
        else
            Session::flash('error', 'Error while input');
        return back();
    }

    public function shipment(Request $request, OfficialTransaction $officialTransaction)
    {
        $is_updated = $officialTransaction->update(
            array(
                'shipment_number' => $request->shipment_number,
                'status' => 'shipped'
            )
        );
        if ($is_updated)
            Session::flash('success', 'Updated');
        else
            Session::flash('error', 'Error while input');
        return back();
    }

    public function received(Request $request, OfficialTransaction $officialTransaction)
    {
        $is_updated = $officialTransaction->update(
            array(
                'status' => 'received'
            )
        );
        if ($is_updated)
            Session::flash('success', 'Updated');
        else
            Session::flash('error', 'Error while confirm');
        return back();
    }

    public function post(Request $request)
    {
        if (!$request->username) {
            return response()->json([
                'status' => 'error',
                'message' => 'username is required',
            ], 400);
        }
        if (!$request->product_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'product_id is required',
            ], 400);
        }
        if (!$request->qty) {
            return response()->json([
                'status' => 'error',
                'message' => 'qty is required',
            ], 400);
        }
        if (!is_numeric($request->product_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'product_id must be numeric',
            ], 400);
        }
        if (!is_numeric($request->qty)) {
            return response()->json([
                'status' => 'error',
                'message' => 'qty must be numeric',
            ], 400);
        }
        $user = \App\Models\User::where('username', $request->username)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'username not found',
            ], 500);
        }
        $product = \App\Models\Product::find($request->product_id);
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'product_id not found',
            ], 500);
        }
        $officialTransaction = OfficialTransaction::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'qty' => $request->qty,
            'poin' => $product->poin * $request->qty,
            'price' => $product->priceUsedByUser($user) * $request->qty,
            'is_turbo' => true,
            'status' => 'received',
        ]);
        return response()->json($officialTransaction, 200);
    }
}
