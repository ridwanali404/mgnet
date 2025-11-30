<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Auth;
use Session;
use App\Models\User;
use DateTime;
use App\Traits\Helper;

class BonusController extends Controller
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
     * @param  \App\Models\Bonus  $bonus
     * @return \Illuminate\Http\Response
     */
    public function show(Bonus $bonus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bonus  $bonus
     * @return \Illuminate\Http\Response
     */
    public function edit(Bonus $bonus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bonus  $bonus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bonus $bonus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bonus  $bonus
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bonus $bonus)
    {
        //
    }

    public function daily2()
    {
        $date = request()->date ?? date('Y-m-d');
        $daily_admin_fee = 10000;
        if (Auth::user()->type == 'admin') {
            $users = User::where('type', 'member')->whereHas('bonuses', function ($q) use ($date) {
                $q->whereIn('type', ['Komisi Sponsor', 'Komisi Monoleg', 'Bonus Generasi'])->whereDate('created_at', $date);
            })->oldest()->get();
            return view('daily2', compact('users', 'daily_admin_fee'));
        }
        $bonuses = Auth::user()->daily($date)->latest()->get();
        return view('daily2', compact('bonuses', 'daily_admin_fee'));
    }

    public function daily()
    {
        $date = request()->date ?? date('Y-m-d');
        $daily_admin_fee = 10000;
        if (Auth::user()->type == 'admin') {
            $users = User::where('type', 'member')->whereHas('bonuses', function ($q) use ($date) {
                $q->whereIn('type', ['Komisi Pasangan', 'Bonus Generasi'])->whereDate('created_at', $date);
            })->oldest()->get();
            return view('daily', compact('users', 'daily_admin_fee'));
        }
        $bonuses = Auth::user()->dailyBonuses($date)->latest()->get();
        return view('daily', compact('bonuses', 'daily_admin_fee'));
    }

    public function dailyConfirm(Request $request, User $user)
    {
        $date = now();
        $user->unpaidDaily($request->date)->update([
            'paid_at' => $date,
        ]);
        Session::flash('success', 'Konfirmasi berhasil');
        return back();
    }

    public function dailyCancel(Request $request, User $user)
    {
        $user->bonuses()->where('paid_at', $request->paid_at)->update([
            'paid_at' => null,
        ]);
        Session::flash('success', 'Batal berhasil');
        return back();
    }

    public function weekly()
    {
        $week = Carbon::parse(request()->week ?? date('Y-\WW'));
        $weekly_admin_fee = 10000;
        if (Auth::user()->type == 'admin') {
            $users = User::where('type', 'member')->whereHas('bonuses', function ($q) use ($week) {
                $q->whereIn('type', ['Komisi Pasangan', 'Bonus Generasi'])->whereBetween(DB::raw('DATE(`created_at`)'), [
                    $week->startofweek()->format('Y-m-d'),
                    $week->endofweek()->format('Y-m-d')
                ]);
            })->oldest()->get();
            return view('weekly', compact('users', 'weekly_admin_fee'));
        }
        $bonuses = Auth::user()->weeklyBonuses($week)->latest()->get();
        return view('weekly', compact('bonuses', 'weekly_admin_fee'));
    }

    public function weeklyConfirm(Request $request, User $user)
    {
        $date = now();
        $user->unpaidWeeklyBonuses($request->week)->update([
            'paid_at' => $date,
        ]);
        Session::flash('success', 'Konfirmasi berhasil');
        return back();
    }

    public function weeklyConfirmBulk(Request $request)
    {
        $date = now();
        $user_ids = explode(',', $request->user_ids);
        foreach ($user_ids as $id) {
            $user = User::find($id);
            $user->unpaidWeeklyBonuses($request->week)->update([
                'paid_at' => $date,
            ]);
        }
        Session::flash('success', 'Konfirmasi berhasil');
        return back();
    }

    public function weeklyCancel(Request $request, User $user)
    {
        $user->bonuses()->where('paid_at', $request->paid_at)->update([
            'paid_at' => null,
        ]);
        Session::flash('success', 'Batal berhasil');
        return back();
    }

    public function weeklyActivate(Request $request)
    {
        $user = Auth::user();
        $week = $request->week;
        $rule = 750000;
        $date = now();
        // $unpaidWeeklyBonusesSum = $user->unpaidWeeklyBonusesSum($week);
        // if ($unpaidWeeklyBonusesSum < $rule) {
        //     Session::flash('fail', 'Saldo Bonus Fast Track belum mencukupi');
        //     return back();
        // }
        // $unpaidWeeklyBonusesAll = $user->unpaidWeeklyBonusesAll($week)->oldest()->get();
        // foreach ($unpaidWeeklyBonusesAll as $bonus) {
        //     if ($bonus->amount < $rule) {
        //         $bonus->update([
        //             'amount' => 0,
        //             'used_amount' => $bonus->amount,
        //             'used_at' => $date,
        //         ]);
        //         $rule -= $bonus->used_amount;
        //     } else {
        //         $bonus->update([
        //             'amount' => $bonus->amount - $rule,
        //             'used_amount' => $rule,
        //             'used_at' => $date,
        //         ]);
        //         break;
        //     }
        // }
        $week_carbon = Carbon::parse($week);
        for ($i = 0; $i < 4; $i++) {
            $week = clone $week_carbon;
            $user->activeWeeks()->create([
                'week' => $week->addWeeks($i)->format('Y-\WW'),
                'method' => 'automaintain',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
        Session::flash('success', 'Konfirmasi berhasil');
        return back();
    }

    public function monthly()
    {
        $month = request()->month ?? date('Y-m');
        $monthly_admin_fee = \App\Models\KeyValue::where('key', 'monthly_admin_fee')->value('value');
        $date = DateTime::createFromFormat('Y-m', $month);
        $closing = \App\Models\MonthlyClosing::whereYear('created_at', date('Y', strtotime(request()->get('month') ?? date('Y-m'))))->whereMonth('created_at', date('m', strtotime(request()->get('month') ?? date('Y-m'))))->count();
        if (in_array(Auth::user()->type, ['admin', 'cradmin'])) {
            $users = User::where('type', 'member')->whereHas('bonuses', function ($q_bonuses) use ($month) {
                $q_bonuses->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->where(function ($q) {
                    $q->where('type', 'Komisi Penjualan');
                    $q->orWhere('type', 'Bonus Unilevel RO');
                    $q->orWhere('type', 'Bonus Royalti Profit Sharing 13%');
                    $q->orWhere('type', 'Bonus Royalti Profit Sharing 70%');
                    $q->orWhere('type', 'Bonus Royalti Profit Sharing 30%');
                });
            })
                ->with(['bank'])
                ->oldest()
                ->get();
            $t = \App\Models\Transaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->where('poin', '>', 0)->whereIn('status', ['paid', 'packed', 'shipped', 'received'])->latest()->get();
            $ot = \App\Models\OfficialTransaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->where('is_topup', false)->where('poin', '>', 0)->whereIn('status', ['paid', 'packed', 'shipped', 'received'])->latest()->get();
            $ott = \App\Models\OfficialTransaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->where('is_topup', true)->where('poin', '>', 0)->whereIn('status', ['paid', 'packed', 'shipped', 'received'])->latest()->get();
            $dp = \App\Models\DailyPoin::whereYear('date', $date->format('Y'))->whereMonth('date', $date->format('m'))->where('pv', '>', 0)->latest()->get();
            return view('monthly', compact('users', 'monthly_admin_fee', 't', 'ot', 'ott', 'dp', 'closing'));
        }
        $bonuses = Auth::user()->monthlyBonuses($month)->latest()->get();
        return view('monthly', compact('bonuses', 'monthly_admin_fee', 'closing'));
    }

    public function monthlyConfirm(Request $request, User $user)
    {
        $date = now();
        $user->unpaidMonthlyBonuses($request->month)->update([
            'paid_at' => $date,
        ]);
        Session::flash('success', 'Konfirmasi berhasil');
        return back();
    }

    public function monthlyConfirmBulk(Request $request)
    {
        $date = now();
        $user_ids = explode(',', $request->user_ids);
        foreach ($user_ids as $id) {
            $user = User::find($id);
            $user->unpaidMonthlyBonuses($request->month)->update([
                'paid_at' => $date,
            ]);
        }
        Session::flash('success', 'Konfirmasi berhasil');
        return back();
    }

    public function monthlyCancel(Request $request, User $user)
    {
        $user->bonuses()->where('paid_at', $request->paid_at)->update([
            'paid_at' => null,
        ]);
        Session::flash('success', 'Batal berhasil');
        return back();
    }
}