<?php

namespace App\Http\Controllers;

use App\Models\Pair;
use Illuminate\Http\Request;
use Session;
use App\Models\KeyValue;
use App\Traits\Helper;
use App\Models\DailyProfit;
use Carbon\Carbon;
use App\Models\Bonus;

class PairController extends Controller
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
        Pair::updateOrCreate([
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
     * @param  \App\Models\Pair  $pair
     * @return \Illuminate\Http\Response
     */
    public function show(Pair $pair)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pair  $pair
     * @return \Illuminate\Http\Response
     */
    public function edit(Pair $pair)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pair  $pair
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pair $pair)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pair  $pair
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pair $pair)
    {
        $pair->delete();
        Session::flash('success', 'Konfigurasi berhasil dihapus');
        return back();
    }

    public function enable()
    {
        return KeyValue::updateOrCreate([
            'key' => 'pair',
        ], [
                'value' => 'enable',
            ]);
    }

    public function disable()
    {
        return KeyValue::updateOrCreate([
            'key' => 'pair',
        ], [
                'value' => 'disable',
            ]);
    }

    public function daily(Request $request)
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        // last dailyProfit
        $lastDailyProfit = DailyProfit::orderBy('date', 'desc')->first();
        if ($lastDailyProfit) {
            if ($lastDailyProfit->date < $request->date) {
                $date = Carbon::parse($lastDailyProfit->date);
                $diff = $date->diffInDays($request->date);
                for ($i = 0; $i < $diff; $i++) {
                    Helper::pair($date->addDay()->format('Y-m-d'));
                }
            }
        } else {
            Helper::pair($request->date);
        }
        Session::flash('success', 'Closing berhasil');
        return back();
    }
}