<?php
namespace App\Traits;

use DateTime;
use Carbon\Carbon;
use App\Models\Pin;
use App\Models\Pair;
use App\Models\Poin;
use App\Models\Rank;
use App\Models\User;
use App\Models\Bonus;
use App\Models\Reward;
use App\Models\UserPin;
use App\Models\KeyValue;
use App\Models\UserPoin;
use App\Models\DailyPoin;
use App\Models\PairReward;
use App\Models\PinHistory;
use App\Models\DailyProfit;
use App\Models\Transaction;
use App\Models\MonthlyClosing;
use App\Models\GlobalDailyPoin;
use App\Models\OfficialTransaction;
use Illuminate\Support\Facades\Mail;

trait Helper
{
    public static function upgrade(UserPin $userPin)
    {
        $user = $userPin->user;
        $pin = $userPin->pin;
        if ($pin->poin_pair || $pin->poin_reward || $pin->poin_ro) {
            $dailyPoin = $user->dailyPoins()->firstOrCreate(['date' => date('Y-m-d')]);
            $dailyPoin->increment('pp', $pin->poin_pair);
            $dailyPoin->increment('pr', $pin->poin_reward);
            $dailyPoin->increment('pv', $pin->poin_ro);
            $globalDailyPoin = GlobalDailyPoin::firstOrCreate(['date' => date('Y-m-d')]);
            $globalDailyPoin->increment('pp', $pin->poin_pair);
            $globalDailyPoin->increment('pr', $pin->poin_reward);
            $globalDailyPoin->increment('pv', $pin->poin_ro);
        }

        // monoleg
        $sponsor = $user->sponsor;
        if (str_contains($pin->name, 'BSM')) {
            if ($sponsor && $sponsor->isMonoleg()) {
                $monolegSponsorCount = $sponsor->monolegSponsors()->count();
                $amount = $pin->bonus_monoleg;
                if ($monolegSponsorCount == 1) {
                    if ($sponsor->monoleg_id) {
                        $user->update([
                            'monoleg_id' => $sponsor->monoleg_id,
                        ]);
                        if ($amount) {
                            $pin_level = $sponsor->monoleg->monolegUserPin->level;
                            if ($pin->level > $pin_level) {
                                $amount = Pin::where('name', 'like', '%BSM%')
                                    ->where('type', 'premium')
                                    ->where('level', $pin_level)
                                    ->value('bonus_monoleg');
                            }
                            $bonus = $sponsor->monoleg->bonuses()->create([
                                'type' => 'Komisi Monoleg',
                                'amount' => $amount,
                                'description' => 'Komisi Monoleg dari penggunaan pin ' . $pin->name . ' oleh ' . $user->username . '.',
                            ]);
                            Helper::automaintain($sponsor->monoleg, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                        }
                    }
                } else if ($monolegSponsorCount > 1) {
                    $user->update([
                        'monoleg_id' => $sponsor->id,
                    ]);
                    if ($amount) {
                        $pin_level = $sponsor->monolegUserPin->level;
                        if ($pin->level > $pin_level) {
                            $amount = Pin::where('name', 'like', '%BSM%')
                                ->where('type', 'premium')
                                ->where('level', $pin_level)
                                ->value('bonus_monoleg');
                        }
                        $bonus = $sponsor->bonuses()->create([
                            'type' => 'Komisi Monoleg',
                            'amount' => $amount,
                            'description' => 'Komisi Monoleg dari penggunaan pin ' . $pin->name . ' oleh ' . $user->username . '.',
                        ]);
                        Helper::automaintain($sponsor, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                    }
                }
            }
            if ($pin->name == 'BSM PLATINUM') {
                Helper::cloneMonolegPlatinum($user);
            }
            // sponsor
            $sponsor = $user->sponsor;
            $amount = $pin->bonus_sponsor;
            if ($sponsor && $amount && $sponsor->premiumUserPin()->count()) {
                $pin_level = $sponsor->userPin->level;
                if ($pin->level > $pin_level) {
                    $amount = Pin::where('name', 'like', '%BSM%')
                        ->where('type', 'premium')
                        ->where('level', $pin_level)
                        ->value('bonus_sponsor') ?? 0;
                }
                $bonus = $sponsor->bonuses()->create([
                    'type' => 'Komisi Sponsor',
                    'amount' => $amount,
                    'description' => 'Komisi Sponsor dari penggunaan pin ' . $pin->name . ' oleh ' . $user->username . '.',
                ]);
                Helper::automaintain($sponsor, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
            }
        } else {
            // sponsor
            $sponsor = $user->sponsor;
            $amount = $pin->bonus_sponsor;
            if ($amount && $sponsor && $sponsor->premiumUserPin()->count()) {
                $bonus = $sponsor->bonuses()->create([
                    'type' => 'Komisi Sponsor',
                    'amount' => $amount,
                    'description' => 'Komisi Sponsor dari penggunaan pin ' . $pin->name . ' oleh ' . $user->username . '.',
                ]);
                Helper::automaintain($sponsor, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
            }
            if ($pin->name == 'PIN PAKET RO') {
                $monoleg = $user->monoleg;
                if ($monoleg) {
                    $bonus = $monoleg->bonuses()->create([
                        'type' => 'Komisi Monoleg',
                        'amount' => $pin->bonus_monoleg,
                        'description' => 'Komisi Monoleg dari penggunaan ' . $pin->name . ' oleh ' . $user->username . '.',
                    ]);
                    Helper::automaintain($monoleg, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                }
            }
        }

        // generasi
        $sponsor = $user->sponsor;
        if ($pin->is_generasi && $pin->price) {
            for ($i = 1; $i <= 10; $i++) {
                if ($sponsor->generasiUserPin) {
                    $amount = KeyValue::where('key', 'weekly_unilevel_' . $i)->value('value');
                    $bonus = $sponsor->bonuses()->create([
                        'type' => 'Bonus Generasi',
                        'amount' => $amount,
                        'description' => 'Bonus Generasi dari upgrade ' . $user->username . '. Generasi ke-' . $i . ' sebesar ' . $amount . '.',
                    ]);
                    Helper::automaintain($sponsor, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                }
                $sponsor = $sponsor->sponsor;
                if (!$sponsor) {
                    break;
                }
            }
        }

        // pp pr update
        $sponsor = $user->sponsor;
        if ($pin->poin_pair || $pin->poin_reward || $pin->poin_ro) {
            while ($sponsor) {
                $dailyPoin = $sponsor->dailyPoins()->firstOrCreate(['date' => date('Y-m-d')]);
                $dailyPoin->increment('pp', $pin->poin_pair);
                $pr = $pin->poin_reward;
                $dailyPoin->increment('pr', $pr);
                $sponsor = $sponsor->sponsor;
            }
        }
    }

    public static function transactionUsers(DateTime $date)
    {
        $t_users = Transaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->where('type', 'general')
            ->where('poin', '>', 0)
            ->whereNotNull('user_id')
            ->pluck('user_id')->toArray();
        $ot_users = OfficialTransaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->where('poin', '>', 0)
            ->pluck('user_id')->toArray();
        $dp_users = DailyPoin::whereYear('date', $date->format('Y'))->whereMonth('date', $date->format('m'))
            ->where('pv', '>', 0)
            ->pluck('user_id')->toArray();
        $user_ids = array_merge($t_users, $ot_users, $dp_users);
        if (KeyValue::where('key', 'poin')->value('value') == 'enable') {
            $poin_users = UserPoin::whereYear('date', $date->format('Y'))->whereMonth('date', $date->format('m'))->pluck('user_id')->toArray();
            $user_ids = array_merge($user_ids, $poin_users);
        }
        return $user_ids;
    }

    public static function transactionPoin(DateTime $date)
    {
        if (KeyValue::where('key', 'poin')->value('value') == 'enable') {
            $poin = Poin::whereYear('date', $date->format('Y'))->whereMonth('date', $date->format('m'))->first();
            if ($poin) {
                return $poin->poin;
            }
        }
        $t = Transaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->sum('poin');
        $ot = OfficialTransaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->sum('poin');
        $gdp = GlobalDailyPoin::whereYear('date', $date->format('Y'))->whereMonth('date', $date->format('m'))
            ->sum('pv');
        return $t + $ot + $gdp;
    }

    public static function isClosing($month)
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        $closing = MonthlyClosing::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->first();
        if ($closing) {
            return true;
        }
        return false;
    }

    public static function pair($date)
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        // Counting Komisi Pasangan
        $pp = GlobalDailyPoin::where('date', $date)->sum('pp');
        if ($pp) {
            $qualifiedUsers = User::whereHas('userPin', function ($q) use ($date) {
                $q->whereIn('name', [
                    'Gold',
                    'Basic Upgrade Gold',
                    'Silver Upgrade Gold',
                    'Platinum',
                    'Basic Upgrade Platinum',
                    'Silver Upgrade Platinum',
                    'Gold Upgrade Platinum',
                    'BSM GOLD',
                    'BSM PLATINUM',
                    'BSM GOLD UP',
                    'BSM PLATINUM UP',
                ])->whereDate('updated_at', '<=', $date);
            })
                ->whereHas('dailyPoinSponsors', function ($q) use ($date) {
                    $q->where('date', $date);
                })
                ->get();
            foreach ($qualifiedUsers as $user) {
                // pair bonus
                $pp_dailyPoinSponsors = $user->dailyPoinSponsors()->where('date', $date)->orderBy('pp', 'desc')->get();
                $pp_before_user = $user->dailyProfits()->where('date', '<', $date)->orderBy('date', 'desc')->first();
                if ($pp_before_user && $pp_before_user->pp_id) {
                    if ($pp_dailyPoinSponsors->where('user_id', $pp_before_user->pp_id)->count()) {
                        $pp_dailyPoinSponsors = $pp_dailyPoinSponsors->map(function ($a) use ($pp_before_user) {
                            if ($a->user_id == $pp_before_user->pp_id) {
                                $a->pp += $pp_before_user->pp_current;
                            }
                            return $a;
                        });
                    } else {
                        $pp_dailyPoinSponsors->push(new DailyPoin([
                            'user_id' => $pp_before_user->pp_id,
                            'pp' => $pp_before_user->pp_current,
                        ]));
                    }
                }
                $pp_l = 0;
                $pp_r = 0;
                $pp_select_before = 'r';
                $pp_select_current = 'l';
                $pp_dailyPoinSponsors = $pp_dailyPoinSponsors->sortByDesc('pp')->values();
                $pp_id = $pp_dailyPoinSponsors->first()->user_id;
                foreach ($pp_dailyPoinSponsors as $key => $dailyPoin) {
                    if ($pp_l <= $pp_r) {
                        $pp_l += $dailyPoin->pp;
                    } else {
                        $pp_r += $dailyPoin->pp;
                    }
                    if ($pp_l > $pp_r) {
                        $pp_select_current = 'l';
                        if ($pp_select_before == $pp_select_current) {
                            $pp_id = $dailyPoin->user_id;
                            $pp_select_before = 'r';
                        }
                    } else {
                        $pp_select_current = 'r';
                        if ($pp_select_before == $pp_select_current) {
                            $pp_id = $dailyPoin->user_id;
                            $pp_select_before = 'l';
                        }
                    }
                }
                $pp_used = min($pp_l, $pp_r);
                $pp_diff = abs($pp_l - $pp_r);

                // reward
                $pr_dailyPoinSponsors = $user->dailyPoinSponsors()->where('date', $date)->orderBy('pr', 'desc')->get();
                $pr_before_user = $user->dailyProfits()->where('date', '<', $date)->orderBy('date', 'desc')->first();
                if ($pr_before_user && $pr_before_user->pr_id) {
                    if ($pr_dailyPoinSponsors->where('user_id', $pr_before_user->pr_id)->count()) {
                        $pr_dailyPoinSponsors = $pr_dailyPoinSponsors->map(function ($a) use ($pr_before_user) {
                            if ($a->user_id == $pr_before_user->pr_id) {
                                $a->pr += $pr_before_user->pr_current;
                            }
                            return $a;
                        });
                    } else {
                        $pr_dailyPoinSponsors->push(new DailyPoin([
                            'user_id' => $pr_before_user->pr_id,
                            'pr' => $pr_before_user->pr_current,
                        ]));
                    }
                }
                $pr_l = 0;
                $pr_r = 0;
                $pr_select_before = 'r';
                $pr_select_current = 'l';
                $pr_dailyPoinSponsors = $pr_dailyPoinSponsors->sortByDesc('pr')->values();
                $pr_id = $pr_dailyPoinSponsors->first()->user_id;
                foreach ($pr_dailyPoinSponsors as $key => $dailyPoin) {
                    if ($pr_l <= $pr_r) {
                        $pr_l += $dailyPoin->pr;
                    } else {
                        $pr_r += $dailyPoin->pr;
                    }
                    if ($pr_l > $pr_r) {
                        $pr_select_current = 'l';
                        if ($pr_select_before == $pr_select_current) {
                            $pr_id = $dailyPoin->user_id;
                            $pr_select_before = 'r';
                        }
                    } else {
                        $pr_select_current = 'r';
                        if ($pr_select_before == $pr_select_current) {
                            $pr_id = $dailyPoin->user_id;
                            $pr_select_before = 'l';
                        }
                    }
                }
                $pr_used = min($pr_l, $pr_r);
                $pr_diff = abs($pr_l - $pr_r);

                // do dailyProfit
                if ($pp_used || $pp_diff || $pr_used || $pr_diff) {
                    $user->dailyProfits()->create([
                        'date' => $date,
                        'pp_used' => $pp_used,
                        'pp_current' => $pp_diff,
                        'pr_used' => $pr_used,
                        'pr_current' => $pr_diff,
                        'pp_id' => $pp_id,
                        'pr_id' => $pr_id,
                    ]);
                }
            }
            // do bonuses
            $pair = DailyProfit::where('date', $date)->sum('pp_used');
            if ($pair) {
                $dailyProfits = DailyProfit::where('date', $date)->where('pp_used', '>', 0)->get();
                $value = 175000 * $pp / $pair;
                if ($value > 100000) {
                    $value = 100000;
                }
                if (KeyValue::where('key', 'pair')->value('value') == 'enable') {
                    $pair = Pair::whereDate('date', $date)->first();
                    if ($pair) {
                        $value = $pair->value;
                    }
                }
                foreach ($dailyProfits as $dailyProfit) {
                    $dailyProfitData = [
                        'type' => 'Komisi Pasangan',
                        'amount' => round($value * $dailyProfit->pp_used),
                        'description' => 'Komisi Pasangan sejumlah ' . $dailyProfit->pp_used . ' pasang dengan nilai Rp ' . number_format($value) . ' per unit.',
                    ];
                    if ($date != date('Y-m-d')) {
                        $dailyProfitData['created_at'] = $date . ' 11:30:00';
                        $dailyProfitData['updated_at'] = $date . ' 11:30:00';
                    }
                    $bonus = $dailyProfit->user->bonuses()->create($dailyProfitData);
                    Helper::automaintain($dailyProfit->user, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                }
            }
            $pair = DailyProfit::where('date', $date)->sum('pr_used');
            if ($pair) {
                $reward_ids = Reward::where('is_platinum', false)->pluck('id');
                $dailyProfits = DailyProfit::where('date', $date)->where('pr_used', '>', 0)->get();
                $pr = GlobalDailyPoin::where('date', $date)->sum('pr');
                $value = 100000 * $pr / $pair;
                if ($value > 100000) {
                    $value = 100000;
                }
                if (KeyValue::where('key', 'pair_reward')->value('value') == 'enable') {
                    $pair = PairReward::whereDate('date', $date)->first();
                    if ($pair) {
                        $value = $pair->value;
                    }
                }
                foreach ($dailyProfits as $dailyProfit) {
                    $dailyProfitData = [
                        'type' => 'Histori Reward',
                        'amount' => round($value * $dailyProfit->pr_used),
                        'description' => 'Reward sejumlah ' . $dailyProfit->pr_used . ' pasang dengan nilai Rp ' . number_format($value) . ' per unit.',
                    ];
                    if ($date != date('Y-m-d')) {
                        $dailyProfitData['created_at'] = $date . ' 11:30:00';
                        $dailyProfitData['updated_at'] = $date . ' 11:30:00';
                    }
                    $bonus = $dailyProfit->user->bonuses()->create($dailyProfitData);
                    Helper::automaintain($dailyProfit->user, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                    $claimed = $dailyProfit->user->userRewards()->whereIn('reward_id', $reward_ids)->count();
                    if ($reward_ids->count() == $claimed && in_array($dailyProfit->user->userPin->pin->name, ['Gold', 'Basic Upgrade Gold', 'Silver Upgrade Gold'])) {
                        continue;
                    }
                    if ($dailyProfit->user->userRewards()->count() == Reward::count()) {
                        continue;
                    }
                    $dailyProfit->user->increment('cash_reward', $dailyProfitData['amount']);
                    $dailyProfit->user->increment('cash_award', $dailyProfitData['amount']);
                }
            }
        }
    }

    public static function cloneMonolegPlatinum($createdUser)
    {
        for ($i = 1; $i <= 2; $i++) {
            $monolegUser = User::create([
                'name' => $createdUser->name,
                'email' => $createdUser->email,
                'username' => $createdUser->username . '_monoleg_' . $i,
                'phone' => $createdUser->phone,
                'bank_id' => $createdUser->bank_id,
                'bank_account' => $createdUser->bank_account,
                'bank_as' => $createdUser->bank_as,
                'ktp' => $createdUser->ktp,
                'npwp' => $createdUser->npwp,
                'password' => $createdUser->password,
                'sponsor_id' => $createdUser->id,
            ]);
            $monolegGoldPin = Pin::where('name', 'BSM GOLD')->first();
            $monolegUserPin = $monolegUser->userPins()->create([
                'buyer_id' => $monolegUser->id,
                'user_id' => $monolegUser->id,
                'pin_id' => $monolegGoldPin->id,
                'code' => strtoupper(str_random(6)),
                'name' => $monolegGoldPin->name,
                'level' => $monolegGoldPin->level,
            ]);
            Helper::pinHistory($monolegUserPin);
            Helper::upgrade($monolegUserPin);
        }
    }

    public static function automaintain($user, $type, $gross, $description)
    {
        Helper::rank($user, $gross);
        $amount = round(0.1 * $gross);
        match ($type) {
            'K' => $user->increment('cash_automaintain', $amount),
            'D' => $user->decrement('cash_automaintain', $amount),
        };
        $user->automaintains()->create([
            'type' => $type,
            'amount' => $amount,
            'current' => $user->cash_automaintain,
            'description' => $description,
        ]);
        // use automaintain
        if ($user->cash_automaintain >= 2000000) {
            $is_already_automaintain = $user->isAlreadyAutomaintain(date('Y-m'));
            if (!$is_already_automaintain) {
                Helper::ro($user);
            }
        }
        // check everyMonth on Kernel.php
    }

    public static function pinHistory($userPin, $qty = 1)
    {
        PinHistory::create([
            'pin_id' => $userPin->pin_id,
            'user_id' => $userPin->buyer_id,
            'qty' => $qty,
            'created_at' => $userPin->created_at,
            'updated_at' => $userPin->updated_at,
        ]);
    }

    public static function ro($user)
    {
        if ($user->userPin->pin->level < 3) {
            $pin = Pin::where('name', 'BSM GOLD Automaintain')->first();
            if (!$pin) {
                return new UserPin();
            }
            $description = 'Penggunaan Pin ' . $pin->name . '.';
        } else {
            $pin = Pin::where('name', 'PIN PAKET RO')->first();
            if (!$pin) {
                return new UserPin();
            }
            $description = 'Penggunaan ' . $pin->name . '.';
        }
        $user->decrement('cash_automaintain', 2000000);
        $user->automaintains()->create([
            'type' => 'D',
            'amount' => 2000000,
            'current' => $user->cash_automaintain,
            'description' => $description,
        ]);
        $userPin = $user->userPins()->create([
            'buyer_id' => $user->id,
            'pin_id' => $pin->id,
            'code' => strtoupper(str_random(6)),
            'name' => $pin->name,
            'price' => $pin->price,
            'level' => $pin->level,
            'is_used' => true,
        ]);
        Helper::pinHistory($userPin);
        Helper::upgrade($userPin);
    }

    public static function rank($user, $nominal)
    {
        $user->increment('cash_rank', $nominal);
        $nextRank = Rank::where('nominal', '>', $user->userRank->rank->nominal ?? 0)->orderBy('nominal')->first();
        if (!$nextRank) {
            return;
        }
        if ($user->cash_rank >= $nextRank->nominal) {
            $user->userRanks()->firstOrCreate([
                'rank_id' => $nextRank->id,
            ]);
        }
    }
}