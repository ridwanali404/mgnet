<?php

namespace App\Http\Controllers;

use App\Models\OfficialTransactionStockist;
use Illuminate\Http\Request;
use Session;

class OfficialTransactionStockistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $product = \App\Models\Product::find($request->product_id);
        OfficialTransactionStockist::create([
            'product_id' => $request->product_id,
            'user_id' => $request->user_id,
            'price' => $product->price_used * $request->qty,
            'poin' => $product->poin * $request->qty,
            'qty' => $request->qty,
            'current' => $request->qty,
        ]);
        Session::flash('success', 'Transaksi berhasil dibuat');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OfficialTransactionStockist  $officialTransactionStockist
     * @return \Illuminate\Http\Response
     */
    public function show(OfficialTransactionStockist $officialTransactionStockist)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OfficialTransactionStockist  $officialTransactionStockist
     * @return \Illuminate\Http\Response
     */
    public function edit(OfficialTransactionStockist $officialTransactionStockist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OfficialTransactionStockist  $officialTransactionStockist
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OfficialTransactionStockist $officialTransactionStockist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OfficialTransactionStockist  $officialTransactionStockist
     * @return \Illuminate\Http\Response
     */
    public function destroy(OfficialTransactionStockist $officialTransactionStockist)
    {
        //
    }
}
