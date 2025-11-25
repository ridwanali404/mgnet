<?php

namespace App\Http\Controllers;

use App\Models\Poin;
use Illuminate\Http\Request;
use Session;
use Carbon\Carbon;
use App\Models\KeyValue;

class PoinController extends Controller
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
        $date = Carbon::createFromFormat('Y-m', $request->date);
        $poin = Poin::whereYear('date', $date->format('Y'))->whereMonth('date', $date->format('m'))->first();
        if ($poin) {
            $poin->update([
                'poin' => $request->poin,
            ]);
        } else {
            $request->request->add([
                'date' => $date->format('Y-m-d'),
            ]);
            Poin::create($request->all());
        }
        Session::flash('success', 'Bulan berhasil dibuat');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Poin  $poin
     * @return \Illuminate\Http\Response
     */
    public function show(Poin $poin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Poin  $poin
     * @return \Illuminate\Http\Response
     */
    public function edit(Poin $poin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Poin  $poin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Poin $poin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Poin  $poin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Poin $poin)
    {
        $poin->delete();
        Session::flash('success', 'Bulan berhasil dihapus');
        return back();
    }

    public function enable()
    {
        return KeyValue::updateOrCreate([
            'key' => 'poin',
        ], [
            'value' => 'enable',
        ]);
    }

    public function disable()
    {
        return KeyValue::updateOrCreate([
            'key' => 'poin',
        ], [
            'value' => 'disable',
        ]);
    }
}
