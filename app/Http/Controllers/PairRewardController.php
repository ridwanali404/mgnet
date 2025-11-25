<?php

namespace App\Http\Controllers;

use App\Models\PairReward;
use Illuminate\Http\Request;
use Session;
use App\Models\KeyValue;

class PairRewardController extends Controller
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
        PairReward::updateOrCreate([
            'date' => $request->date,
        ], [
            'poin' => $request->poin,
            'pair' => $request->pair,
            'value' => $request->value,
        ]);
        Session::flash('success', 'Konfigurasi berhasil dibuat');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PairReward  $pairReward
     * @return \Illuminate\Http\Response
     */
    public function show(PairReward $pairReward)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PairReward  $pairReward
     * @return \Illuminate\Http\Response
     */
    public function edit(PairReward $pairReward)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PairReward  $pairReward
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PairReward $pairReward)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PairReward  $pairReward
     * @return \Illuminate\Http\Response
     */
    public function destroy(PairReward $pairReward)
    {
        $pairReward->delete();
        Session::flash('success', 'Konfigurasi berhasil dihapus');
        return back();
    }

    public function enable()
    {
        return KeyValue::updateOrCreate([
            'key' => 'pair_reward',
        ], [
            'value' => 'enable',
        ]);
    }

    public function disable()
    {
        return KeyValue::updateOrCreate([
            'key' => 'pair_reward',
        ], [
            'value' => 'disable',
        ]);
    }
}
