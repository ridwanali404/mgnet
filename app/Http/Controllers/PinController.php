<?php

namespace App\Http\Controllers;

use App\Models\Pin;
use Illuminate\Http\Request;
use Session;

class PinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pins = Pin::orderBy('type')->orderBy('price')->get();
        return view('pin', compact('pins'));
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
        Pin::create($request->all());
        Session::flash('success', 'Pin dibuat');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pin  $pin
     * @return \Illuminate\Http\Response
     */
    public function show(Pin $pin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pin  $pin
     * @return \Illuminate\Http\Response
     */
    public function edit(Pin $pin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pin  $pin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pin $pin)
    {
        $pin->update($request->all());
        Session::flash('success', 'Pin diubah');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pin  $pin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pin $pin)
    {
        $pin->delete();
        Session::flash('success', 'Pin dihapus');
        return back();
    }
}
