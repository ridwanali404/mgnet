<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use App\Models\Award;
use App\Models\Bonus;
use App\Models\UserAward;
use Illuminate\Http\Request;

class UserAwardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->type == 'admin') {
            $userAwards = UserAward::where('is_paid', false)->oldest()->get();
            $userAwardHistories = UserAward::where('is_paid', true)->orderBy('updated_at', 'desc')->get();
            $userAwardBonuses = Bonus::where('type', 'Histori Reward')->whereDate('created_at', request()->date ?? date('Y-m-d'))->latest()->get();
        } else {
            $userAwards = Auth::user()->userAwards()->where('is_paid', false)->oldest()->get();
            $userAwardHistories = Auth::user()->userAwards()->where('is_paid', true)->orderBy('updated_at', 'desc')->get();
            $userAwardBonuses = Auth::user()->bonuses()->where('type', 'Histori Reward')->whereDate('created_at', request()->date ?? date('Y-m-d'))->latest()->get();
        }
        return view('userAward', compact('userAwards', 'userAwardHistories', 'userAwardBonuses'));
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserAward  $userAward
     * @return \Illuminate\Http\Response
     */
    public function show(UserAward $userAward)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserAward  $userAward
     * @return \Illuminate\Http\Response
     */
    public function edit(UserAward $userAward)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserAward  $userAward
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserAward $userAward)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserAward  $userAward
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserAward $userAward)
    {
        //
    }

    public function confirm(Request $request, UserAward $userAward)
    {
        $userAward->update([
            'is_paid' => true,
        ]);
        Session::flash('success', 'Konfirmasi berhasil');
        return back();
    }

    public function claim(Request $request, Award $award)
    {
        Auth::user()->decrement('cash_award', $award->nominal);
        Auth::user()->userAwards()->create([
            'award_id' => $award->id,
        ]);
        Session::flash('success', 'Konfirmasi berhasil');
        return back();
    }
}