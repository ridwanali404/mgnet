<?php

namespace App\Http\Controllers;

use App\Models\BigProduct;
use Illuminate\Http\Request;
use App\Models\Product;
use Session;

class BigProductController extends Controller
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
        $product = Product::find($request->child_product_id);
        $request->request->add([
            'product_name' => $product->name,
            'product_poin' => $product->poin,
        ]);
        BigProduct::create($request->all());
        Session::flash('success', 'Produk berhasil ditambahkan');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BigProduct  $bigProduct
     * @return \Illuminate\Http\Response
     */
    public function show(BigProduct $bigProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BigProduct  $bigProduct
     * @return \Illuminate\Http\Response
     */
    public function edit(BigProduct $bigProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BigProduct  $bigProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BigProduct $bigProduct)
    {
        $product = Product::find($request->child_product_id);
        $request->request->add([
            'product_name' => $product->name,
            'product_poin' => $product->poin,
        ]);
        $bigProduct->update($request->all());
        Session::flash('success', 'Sub Produk berhasil diubah');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BigProduct  $bigProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(BigProduct $bigProduct)
    {
        $bigProduct->delete();
        Session::flash('success', 'Sub Produk berhasil dihapus');
        return back();
    }
}
