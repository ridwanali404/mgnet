<?php

namespace App\Http\Controllers;

use App\Models\DailyProfit;
use Illuminate\Http\Request;
use App\Models\KeyValue;
use App\Models\Pair;
use App\Models\PairReward;
use App\Models\GlobalDailyPoin;
use App\Models\User;

class DailyProfitController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DailyProfit  $dailyProfit
     * @return \Illuminate\Http\Response
     */
    public function show(DailyProfit $dailyProfit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DailyProfit  $dailyProfit
     * @return \Illuminate\Http\Response
     */
    public function edit(DailyProfit $dailyProfit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DailyProfit  $dailyProfit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DailyProfit $dailyProfit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DailyProfit  $dailyProfit
     * @return \Illuminate\Http\Response
     */
    public function destroy(DailyProfit $dailyProfit)
    {
        //
    }

    public function pair()
    {
        if (KeyValue::where('key', 'pair')->value('value') == 'enable') {
            $pair = Pair::whereDate('date', request()->date)->first();
            if ($pair) {
                return response()->json([
                    'pp' => number_format($pair->poin),
                    'pair' => number_format($pair->pair),
                    'value' => number_format($pair->value),
                ]);
            }
        }
        $pp = GlobalDailyPoin::where('date', request()->date)->sum('pp');
        $dailyProfitCount = DailyProfit::whereDate('date', request()->date)->count();
        if ($dailyProfitCount) {
            $pair = DailyProfit::whereDate('date', request()->date)->sum('pp_used');
        } else {
            $pair = 0;
            $qualifiedUsers = User::whereHas('userPin', function ($q) {
                $q->whereHas('pin', function ($q_pin) {
                    $q_pin->whereIn('name', ['Gold', 'Basic Upgrade Gold', 'Silver Upgrade Gold', 'Platinum', 'Basic Upgrade Platinum', 'Silver Upgrade Platinum', 'Gold Upgrade Platinum']);
                })->whereDate('updated_at', '<=', request()->date);
            })->get();
            foreach ($qualifiedUsers as $user) {
                // pair bonus
                $pp_dailyPoinSponsors = $user->dailyPoinSponsors()->whereDate('date', request()->date)->orderBy('pp', 'desc')->get();
                $pp_before = $user->dailyProfits()->whereDate('date', '<', request()->date)->orderBy('date', 'desc')->value('pp_current') ?? 0;
                $pp_l = $pp_before;
                $pp_r = 0;
                foreach ($pp_dailyPoinSponsors as $dailyPoin) {
                    if ($pp_l <= $pp_r) {
                        $pp_l += $dailyPoin->pp;
                    } else {
                        $pp_r += $dailyPoin->pp;
                    }
                }
                $pp_used = min($pp_l, $pp_r);
                if ($pp_used) {
                    if ($pp_used > $user->userPin->pin->pair_flush) {
                        $pp_used = $user->userPin->pin->pair_flush;
                    }
                }
                $pair += $pp_used;
            }
        }
        $value = 0;
        if ($pair) {
            $value = 100000 * $pp / $pair;
            if ($value > 100000) {
                $value = 100000;
            }
        }
        return response()->json([
            'pp' => number_format($pp),
            'pair' => number_format($pair),
            'value' => number_format($value),
        ]);
    }

    public function pairReward()
    {
        if (KeyValue::where('key', 'pair_reward')->value('value') == 'enable') {
            $pair = PairReward::whereDate('date', request()->date)->first();
            if ($pair) {
                return response()->json([
                    'pr' => number_format($pair->poin),
                    'pair' => number_format($pair->pair),
                    'value' => number_format($pair->value),
                ]);
            }
        }
        $pr = GlobalDailyPoin::where('date', request()->date)->sum('pr');
        $dailyProfitCount = DailyProfit::whereDate('date', request()->date)->count();
        if ($dailyProfitCount) {
            $pair = DailyProfit::whereDate('date', request()->date)->sum('pr_used');
        } else {
            $pair = 0;
            $qualifiedUsers = User::whereHas('userPin', function ($q) {
                $q->whereHas('pin', function ($q_pin) {
                    $q_pin->whereIn('name', ['Gold', 'Basic Upgrade Gold', 'Silver Upgrade Gold', 'Platinum', 'Basic Upgrade Platinum', 'Silver Upgrade Platinum', 'Gold Upgrade Platinum']);
                })->whereDate('updated_at', '<=', request()->date);
            })->get();
            foreach ($qualifiedUsers as $user) {
                // pair bonus
                $pr_dailyPoinSponsors = $user->dailyPoinSponsors()->whereDate('date', request()->date)->orderBy('pr', 'desc')->get();
                $pr_before = $user->dailyProfits()->whereDate('date', '<', request()->date)->orderBy('date', 'desc')->value('pr_current') ?? 0;
                $pr_l = $pr_before;
                $pr_r = 0;
                foreach ($pr_dailyPoinSponsors as $dailyPoin) {
                    if ($pr_l <= $pr_r) {
                        $pr_l += $dailyPoin->pr;
                    } else {
                        $pr_r += $dailyPoin->pr;
                    }
                }
                $pr_used = min($pr_l, $pr_r);
                if ($pr_used) {
                    if ($pr_used > $user->userPin->pin->pair_flush) {
                        $pr_used = $user->userPin->pin->pair_flush;
                    }
                }
                $pair += $pr_used;
            }
        }
        $value = 0;
        if ($pair) {
            $value = 100000 * $pr / $pair;
            if ($value > 100000) {
                $value = 100000;
            }
        }
        return response()->json([
            'pr' => number_format($pr),
            'pair' => number_format($pair),
            'value' => number_format($value),
        ]);
    }
}