<?php

namespace App\Http\Controllers;

use App\Models\MonthlyClosing;
use Illuminate\Http\Request;
use DateTime;
use Session;
use App\Models\KeyValue;
use App\Models\User;
use App\Models\Bonus;
use App\Models\Transaction;
use App\Models\OfficialTransaction;
use Carbon\Carbon;
use App\Traits\Helper;
use App\Models\DailyPoin;

class MonthlyClosingController extends Controller
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
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        $month = $request->month;
        $date = DateTime::createFromFormat('Y-m', $month);

        // check if it was closing
        if (MonthlyClosing::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->count()) {
            Session::flash('fail', 'Closing sudah pernah dilakukan');
            return back();
        }

        // get total omset perusahaan
        $omset_13 = Helper::transactionPoin($date) * 1000 * 13 / 100;

        // check qualified user
        $users = User::whereIn('id', Helper::transactionUsers($date))->get()->filter(function ($a) use ($month) {
            return $a->monthlyRoyaltyQualified($month) == true;
        });
        // count qualified
        $total_13 = $users->count();
        $bonus_13 = $total_13 ? floor($omset_13 / $total_13) : 0;
        // do bonus
        foreach ($users as $user) {
            $user->bonuses()->create([
                'type' => 'Bonus Royalti Profit Sharing 13%',
                'amount' => round($bonus_13),
                'description' => 'Bonus Royalti Profit Sharing 13%.',
                'created_at' => $month.'-01 00:00:00',
                'updated_at' => $month.'-01 00:00:00',
            ]);
        }

        // do ro bonus
        $potency = collect();
        // transaction
        $transactions = Transaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))
            ->where('type', 'general')
            ->where('poin', '>', 0)
            ->whereNotNull('user_id')
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->latest();
        $userIdArray = clone $transactions;
        $t_users = $userIdArray->groupBy('user_id')->pluck('user_id');
        foreach ($t_users as $userId) {
            $user = User::find($userId);
            $sponsor = $user->sponsor;
            $i = 1;
            while ($i <= 10 && $sponsor) {
                $percent = KeyValue::where('key', 'monthly_ro_unilevel_'.$i)->value('value');
                $userTransactions = clone $transactions;
                $userTransactions = $userTransactions->where('user_id', $userId)->get();
                foreach ($userTransactions as $ut) {
                    $carts = '';
                    foreach ($ut->carts as $key => $cart) {
                        if ($key + 1 == $ut->carts()->count()) {
                            if ($key == 0) {
                                $carts .= $cart->qty.' '. ($cart->product ? $cart->product->name : $cart->name ?? 'Produk telah dihapus').' ('.$cart->poin_total.' poin)';
                            } else {
                                $carts .= 'dan '.$cart->qty.' '. ($cart->product ? $cart->product->name : $cart->name ?? 'Produk telah dihapus').' ('.$cart->poin_total.' poin)';
                            }
                        } else {
                            $carts .= $cart->qty.' '. ($cart->product ? $cart->product->name : $cart->name ?? 'Produk telah dihapus').' ('.$cart->poin_total.' poin)'.', ';
                        }
                    }
                    $potency->push([
                        'user_id' => $sponsor->id,
                        'type' => 'Bonus Unilevel RO',
                        'amount' => round($ut->poin * 1000 * $percent / 100),
                        'description' => 'Bonus Unilevel RO dari belanja '.$user->username.'. Belanja '.$carts. '. Generasi ke-'.$i.' sebesar '.$percent.'% dari '.$ut->poin.' poin.',
                        'created_at' => $ut->created_at,
                    ]);
                }
                if (!$sponsor->member) {
                    break;
                }
                if ($sponsor->member->member_phase_name != 'User Free' && $sponsor->monthlyQualified($month)) {
                    $i++;
                }
                $sponsor = $sponsor->sponsor;
            }
        }
        // official transaction
        $ot = OfficialTransaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->whereIn('status', ['paid', 'packed', 'shipped', 'received'])->latest();
        $userIdArray = clone $ot;
        $ot_users = $userIdArray->groupBy('user_id')->pluck('user_id');
        foreach ($ot_users as $userId) {
            $user = User::find($userId);
            $sponsor = $user->sponsor;
            $i = 1;
            while ($i <= 10 && $sponsor) {
                $percent = KeyValue::where('key', 'monthly_ro_unilevel_'.$i)->value('value');
                $userTransactions = clone $ot;
                $userTransactions = $userTransactions->where('user_id', $userId)->get();
                foreach ($userTransactions as $ut) {
                    $potency->push([
                        'user_id' => $sponsor->id,
                        'type' => 'Bonus Unilevel RO',
                        'amount' => round($ut->poin * 1000 * $percent / 100),
                        'description' => 'Bonus Unilevel RO dari belanja official '.$user->username.'. Belanja '.$ut->qty.' '.($ut->product->name ?? 'Produk telah dihapus').' ('.$ut->poin.' poin)'.'. Generasi ke-'.$i.' sebesar '.$percent.'% dari '.$ut->poin.' poin.',
                        'created_at' => $ut->created_at,
                    ]);
                }
                if (!$sponsor->member) {
                    break;
                }
                if ($sponsor->member->member_phase_name != 'User Free' && $sponsor->monthlyQualified($month)) {
                    $i++;
                }
                $sponsor = $sponsor->sponsor;
            }
        }
        // pin
        $users = User::whereHas('userPin', function ($q) {
            $q->whereHas('pin', function ($q_pin) {
                $q_pin->whereIn('name', ['Gold', 'Basic Upgrade Gold', 'Silver Upgrade Gold', 'Platinum', 'Basic Upgrade Platinum', 'Silver Upgrade Platinum', 'Gold Upgrade Platinum']);
            });
        })->whereHas('dailyPoins', function ($q) {
            $q->where('pv', '>', 0);
        })->get();
        foreach ($users as $user) {
            $sponsor = $user->sponsor;
            $i = 1;
            while ($i <= 10 && $sponsor) {
                $percent = \App\Models\KeyValue::where('key', 'monthly_ro_unilevel_'.$i)->value('value');
                $dp = $user->dailyPoins()->where('pv', '>', 0)->whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->latest()->get();
                foreach ($dp as $a) {
                    $potency->push([
                        'user_id' => $sponsor->id,
                        'type' => 'Bonus Unilevel RO',
                        'amount' => round($a->pv * 1000 * $percent / 100),
                        'description' => 'Bonus Unilevel RO dari paket pin '.$a->user->username.' sejumlah '.$a->pv.' poin'.'. Generasi ke-'.$i.' sebesar '.$percent.'% dari '.$a->pv.' poin.',
                        'created_at' => $a->created_at,
                    ]);
                }
                if (!$sponsor->member) {
                    break;
                }
                if ($sponsor->member->member_phase_name != 'User Free' && $sponsor->monthlyQualified($month)) {
                    $i++;
                }
                $sponsor = $sponsor->sponsor;
            }
        }
        // do
        foreach ($potency as $a) {
            Bonus::create($a);
        }

        // passup
        $userPassUpCollections = collect();
        $user_ids = array_unique(array_merge($t_users->toArray(), $ot_users->toArray()), SORT_REGULAR);
        foreach ($user_ids as $user_id) {
            $user = User::find($user_id);
            if (!$user->monthlyQualified($month)) {
                $poin = $user->monthlyPoin($month);
                if ($poin) {
                    $sponsor = $user->sponsor;
                    $i = 1;
                    while ($sponsor) {
                        if ($sponsor->monthlyQualified($month)) {
                            $userPassUp = $userPassUpCollections->firstWhere('user_id', $sponsor->id);
                            if ($poin) {
                                if ($userPassUp) {
                                    $userPassUp['poin'] += $poin;
                                } else {
                                    $userPassUpCollections->push([
                                        'user_id' => $sponsor->id,
                                        'poin' => $poin,
                                    ]);
                                }
                            }
                            break;
                        }
                        $sponsor = $sponsor->sponsor;
                    }
                }
            }
        }

        foreach ($userPassUpCollections as $a) {
            Bonus::create([
                'user_id' => $a['user_id'],
                'type' => 'Bonus Unilevel RO',
                'amount' => $a['poin'] * 130, // 130 = 1000 * 13%
                'description' => 'Bonus Unilevel RO dari Pass Up dengan jumlah 13% dari ' . $a['poin'] . ' poin.',
                'created_at' => $month.'-01 00:00:00',
            ]);
        }

        // create closing
        MonthlyClosing::create([
            'created_at' => $month.'-01 00:00:00',
            'updated_at' => $month.'-01 00:00:00',
        ]);

        Session::flash('success', 'Closing berhasil');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MonthlyClosing  $monthlyClosing
     * @return \Illuminate\Http\Response
     */
    public function show(MonthlyClosing $monthlyClosing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MonthlyClosing  $monthlyClosing
     * @return \Illuminate\Http\Response
     */
    public function edit(MonthlyClosing $monthlyClosing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MonthlyClosing  $monthlyClosing
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MonthlyClosing $monthlyClosing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MonthlyClosing  $monthlyClosing
     * @return \Illuminate\Http\Response
     */
    public function destroy(MonthlyClosing $monthlyClosing)
    {
        //
    }

    public function poin()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        $month = request()->month ?? date('Y-m');
        $date = DateTime::createFromFormat('Y-m', $month);

        $transactions = Transaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))
            ->where('type', 'general')
            ->where('poin', '>', 0)
            ->whereNull('user_id')
            ->whereNotNull('sponsor_id')
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->latest();
        $userIdArray = clone $transactions;
        $users = $userIdArray->groupBy('sponsor_id')->pluck('sponsor_id')->toArray();

        $ot = OfficialTransaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->whereIn('status', ['paid', 'packed', 'shipped', 'received'])->latest();
        $userIdArray = clone $ot;
        $users2 = $userIdArray->groupBy('user_id')->pluck('user_id')->toArray();
        $user_ids = array_unique(array_merge($users, $users2), SORT_REGULAR);
        foreach ($user_ids as $key => $user_id) {
            $user = User::find($user_id);
            $poin = $user->monthlyPoin($month);
            if ($poin < 39) {
                // find who is upper qualified
                $is_found = false;
                $sponsor = $user->sponsor;
                $i = 1;
                while ($sponsor) {
                    if ($sponsor->monthlyQualified($month)) {
                        $is_found = true;
                        $found = $sponsor;
                        break;
                    }
                    $sponsor = $sponsor->sponsor;
                }
            }
            print(($key + 1). ' | ' . $user->username. ' | ' . $poin . ' | ' . ($poin >= 39 ? 'Bonus RO Qualified' : 'Bonus RO Not Qualified') . ' | ' . ($poin >= 250 ? 'Bonus Royalti Qualified' : 'Bonus Royalti Not Qualified') .  ' | ' . ($poin < 39 ? ($is_found ? ($sponsor->username . ' is Upper Qualified') : 'Upper Qualified Not Found') : '') . '<br>' );

        }
        // dd($user_ids);
    }
}
