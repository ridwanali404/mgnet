<?php

namespace App\Http\Controllers;

use App\Models\UserReward;
use App\Models\Reward;
use App\Models\Bonus;
use Illuminate\Http\Request;
use Session;
use Auth;

class UserRewardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->type == 'admin') {
            $userRewards = UserReward::where('is_paid', false)->oldest()->get();
            $userRewardHistories = UserReward::where('is_paid', true)->orderBy('updated_at', 'desc')->get();
            $userRewardBonuses = Bonus::where('type', 'Histori Reward')->whereDate('created_at', request()->date ?? date('Y-m-d'))->latest()->get();
        } else {
            $userRewards = Auth::user()->userRewards()->where('is_paid', false)->oldest()->get();
            $userRewardHistories = Auth::user()->userRewards()->where('is_paid', true)->orderBy('updated_at', 'desc')->get();
            $userRewardBonuses = Auth::user()->bonuses()->where('type', 'Histori Reward')->latest()->get();
        }
        return view('userReward', compact('userRewards', 'userRewardHistories', 'userRewardBonuses'));
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
     * @param  \App\Models\UserReward  $userReward
     * @return \Illuminate\Http\Response
     */
    public function show(UserReward $userReward)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserReward  $userReward
     * @return \Illuminate\Http\Response
     */
    public function edit(UserReward $userReward)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserReward  $userReward
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserReward $userReward)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserReward  $userReward
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserReward $userReward)
    {
        //
    }

    public function confirm(Request $request, UserReward $userReward)
    {
        $userReward->update([
            'is_paid' => true,
        ]);
        Session::flash('success', 'Konfirmasi berhasil');
        return back();
    }

    public function claim(Request $request, Reward $reward)
    {
        Auth::user()->decrement('cash_reward', $reward->nominal);
        Auth::user()->userRewards()->create([
            'reward_id' => $reward->id,
        ]);
        Session::flash('success', 'Konfirmasi berhasil');
        return back();
    }
}