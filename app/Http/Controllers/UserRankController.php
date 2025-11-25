<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\Rank;
use App\Models\Bonus;
use App\Models\UserRank;
use Illuminate\Http\Request;

class UserRankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->type == 'admin') {
            $userRanks = UserRank::latest()->get();
            $userRankBonuses = Bonus::whereIn('type', ['Komisi Sponsor', 'Komisi Monoleg', 'Komisi Pasangan', 'Bonus Generasi'])->whereDate('created_at', request()->date ?? date('Y-m-d'))->latest()->get();
        } else {
            $userRanks = Auth::user()->userRanks()->latest()->get();
            $userRankBonuses = Auth::user()->bonuses()->whereIn('type', ['Komisi Sponsor', 'Komisi Monoleg', 'Komisi Pasangan', 'Bonus Generasi'])->whereDate('created_at', request()->date ?? date('Y-m-d'))->latest()->get();
        }
        return view('userRank', compact('userRanks', 'userRankBonuses'));
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
     * @param  \App\Models\UserRank  $userRank
     * @return \Illuminate\Http\Response
     */
    public function show(UserRank $userRank)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserRank  $userRank
     * @return \Illuminate\Http\Response
     */
    public function edit(UserRank $userRank)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserRank  $userRank
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserRank $userRank)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserRank  $userRank
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserRank $userRank)
    {
        //
    }
}