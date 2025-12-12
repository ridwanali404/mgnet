<?php

namespace App\Http\Controllers;

use App\Models\TripReward;
use Illuminate\Http\Request;
use Session;

class TripRewardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tripRewards = TripReward::orderBy('nominal')->get();
        return view('tripReward', compact('tripRewards'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        TripReward::create($request->all());
        Session::flash('success', 'Trip Reward dibuat');
        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TripReward $tripReward)
    {
        $tripReward->update($request->all());
        Session::flash('success', 'Trip Reward diubah');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TripReward $tripReward)
    {
        $tripReward->delete();
        Session::flash('success', 'Trip Reward dihapus');
        return back();
    }
}
