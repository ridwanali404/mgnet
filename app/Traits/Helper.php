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
use App\Models\PowerPlusQualification;
use App\Models\ProfitSharing;
use App\Models\UmrohTripSaving;
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
            if ($sponsor && $sponsor->premiumUserPin()->count()) {
                // Gunakan persentase jika ada, jika tidak gunakan nominal untuk backward compatibility
                if ($pin->bonus_sponsor_percent > 0) {
                    $amount = round($pin->price * $pin->bonus_sponsor_percent / 100);
                } else {
                    $amount = $pin->bonus_sponsor;
                }
                if ($amount > 0) {
                    $pin_level = $sponsor->userPin->level;
                    if ($pin->level > $pin_level) {
                        $sponsorPin = Pin::where('name', 'like', '%BSM%')
                            ->where('type', 'premium')
                            ->where('level', $pin_level)
                            ->first();
                        if ($sponsorPin) {
                            if ($sponsorPin->bonus_sponsor_percent > 0) {
                                $amount = round($pin->price * $sponsorPin->bonus_sponsor_percent / 100);
                            } else {
                                $amount = $sponsorPin->bonus_sponsor ?? 0;
                            }
                        }
                    }
                    $bonus = $sponsor->bonuses()->create([
                        'type' => 'Komisi Sponsor',
                        'amount' => $amount,
                        'description' => 'Komisi Sponsor dari penggunaan pin ' . $pin->name . ' oleh ' . $user->username . '.',
                    ]);
                    Helper::automaintain($sponsor, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                }
            }
        } else {
            // Bonus Sponsor 15% - DIBUAT HARIAN (dibayar langsung saat upgrade)
            // Skip bonus sponsor untuk pin upgrade (type = 'upgrade')
            if ($pin->type != 'upgrade') {
                $sponsor = $user->sponsor;
                if ($sponsor && $sponsor->premiumUserPin()->count()) {
                    // Gunakan persentase jika ada, jika tidak gunakan nominal untuk backward compatibility
                    if ($pin->bonus_sponsor_percent > 0) {
                        $amount = round($pin->price * $pin->bonus_sponsor_percent / 100);
                    } else {
                        $amount = $pin->bonus_sponsor;
                    }
                    if ($amount > 0) {
                        $bonus = $sponsor->bonuses()->create([
                            'type' => 'Komisi Sponsor',
                            'amount' => $amount,
                            'description' => 'Komisi Sponsor dari penggunaan pin ' . $pin->name . ' oleh ' . $user->username . '.',
                        ]);
                        Helper::automaintain($sponsor, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                    }
                }
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

        // Bonus Generasi 19% - DIBUAT HARIAN (dibayar langsung saat upgrade)
        $sponsor = $user->sponsor;
        if ($pin->is_generasi && $pin->price && $pin->generasi_percent > 0) {
            // Hitung total alokasi bonus generasi (19% dari harga paket)
            $totalAllocation = round($pin->price * $pin->generasi_percent / 100);
            
            // Distribusi persentase per generasi: 25%, kemudian turun sampai 3%
            // Generasi 1: 25%, 2: 20%, 3: 15%, 4: 12%, 5: 10%, 6: 8%, 7: 6%, 8: 5%, 9: 4%, 10: 3%
            $generasiPercentages = [25, 20, 15, 12, 10, 8, 6, 5, 4, 3];
            
            // Track untuk push-up mechanism
            $generasiStack = []; // Stack untuk menyimpan generasi yang perlu di-push-up
            
            for ($i = 1; $i <= 10; $i++) {
                if (!$sponsor) {
                    break;
                }
                
                // Cek apakah sponsor punya pin generasi (Gold atau Platinum)
                if ($sponsor->premiumUserPin && $sponsor->premiumUserPin->pin) {
                    $sponsorPin = $sponsor->premiumUserPin->pin;
                    
                    // Push-up mechanism: Jika di bawah Gold terdapat Platinum, selisih naik ke upline Platinum
                    if ($sponsorPin->name == 'Gold' && $pin->name == 'Platinum') {
                        // Cari upline Platinum terdekat di atas Gold ini
                        $platinumUpline = Helper::findPlatinumUpline($sponsor);
                        if ($platinumUpline) {
                            // Push-up: bonus Platinum diberikan ke upline Platinum, bukan ke Gold
                            $percent = $generasiPercentages[$i - 1] ?? 0;
                            $amount = round($totalAllocation * $percent / 100);
                            
                            if ($amount > 0) {
                                $bonus = $platinumUpline->bonuses()->create([
                                    'type' => 'Bonus Generasi',
                                    'amount' => $amount,
                                    'description' => 'Bonus Generasi dari upgrade ' . $user->username . ' paket ' . $pin->name . '. Generasi ke-' . $i . ' (Push-up dari ' . $sponsor->username . ' Gold) sebesar ' . $percent . '% dari alokasi (Rp ' . number_format($totalAllocation, 0, ',', '.') . ').',
                                ]);
                                Helper::automaintain($platinumUpline, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                            }
                            // Skip Gold ini, lanjut ke sponsor berikutnya
                            $sponsor = $sponsor->sponsor;
                            continue;
                        }
                    }
                    
                    // Push-up mechanism: Jika akun tidak aktif 90 hari, push-up ke upline aktif
                    $isInactive90Days = false;
                    if (!$sponsor->is_active) {
                        $isInactive90Days = true;
                    } elseif ($sponsor->active_until) {
                        // Cek apakah sudah melewati 90 hari dari active_until
                        $inactiveDate = Carbon::parse($sponsor->active_until);
                        if ($inactiveDate->addDays(90)->lt(Carbon::now())) {
                            $isInactive90Days = true;
                        }
                    }
                    
                    if ($isInactive90Days) {
                        // Cari upline aktif terdekat
                        $activeUpline = Helper::findActiveUpline($sponsor);
                        if ($activeUpline && $activeUpline->id != $sponsor->id) {
                            // Push-up ke upline aktif
                            $percent = $generasiPercentages[$i - 1] ?? 0;
                            $amount = round($totalAllocation * $percent / 100);
                            
                            if ($amount > 0 && $activeUpline->premiumUserPin && in_array($activeUpline->premiumUserPin->pin->name, ['Gold', 'Platinum'])) {
                                $bonus = $activeUpline->bonuses()->create([
                                    'type' => 'Bonus Generasi',
                                    'amount' => $amount,
                                    'description' => 'Bonus Generasi dari upgrade ' . $user->username . ' paket ' . $pin->name . '. Generasi ke-' . $i . ' (Push-up dari ' . $sponsor->username . ' tidak aktif 90 hari) sebesar ' . $percent . '% dari alokasi (Rp ' . number_format($totalAllocation, 0, ',', '.') . ').',
                                ]);
                                Helper::automaintain($activeUpline, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                            }
                            // Skip sponsor tidak aktif, lanjut ke sponsor berikutnya
                            $sponsor = $sponsor->sponsor;
                            continue;
                        }
                    }
                    
                    // Normal flow: berikan bonus jika sponsor punya Gold atau Platinum
                    if (in_array($sponsorPin->name, ['Gold', 'Platinum'])) {
                        $percent = $generasiPercentages[$i - 1] ?? 0;
                        $amount = round($totalAllocation * $percent / 100);
                        
                        if ($amount > 0) {
                            $bonus = $sponsor->bonuses()->create([
                                'type' => 'Bonus Generasi',
                                'amount' => $amount,
                                'description' => 'Bonus Generasi dari upgrade ' . $user->username . ' paket ' . $pin->name . '. Generasi ke-' . $i . ' sebesar ' . $percent . '% dari alokasi (Rp ' . number_format($totalAllocation, 0, ',', '.') . ').',
                            ]);
                            Helper::automaintain($sponsor, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                        }
                    }
                }
                
                $sponsor = $sponsor->sponsor;
            }
        }

        // Bonus Monoleg 9% untuk Gold & Platinum (bukan BSM) - DIBUAT HARIAN (dibayar langsung saat upgrade)
        if (!str_contains($pin->name, 'BSM') && in_array($pin->name, ['Gold', 'Platinum']) && $pin->monoleg_percent > 0) {
            $sponsor = $user->sponsor;
            // Syarat: sponsor harus memiliki 1 sponsor langsung
            if ($sponsor && $sponsor->sponsors()->whereHas('premiumUserPin')->count() >= 1) {
                // Cari monoleg (leg kanan) - unlimited depth
                $monoleg = Helper::findMonolegRecursive($sponsor, $user);
                if ($monoleg) {
                    $amount = round($pin->price * $pin->monoleg_percent / 100);
                    if ($amount > 0) {
                        $bonus = $monoleg->bonuses()->create([
                            'type' => 'Bonus Monoleg',
                            'amount' => $amount,
                            'description' => 'Bonus Monoleg 9% dari upgrade ' . $user->username . ' paket ' . $pin->name . '.',
                        ]);
                        Helper::automaintain($monoleg, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                    }
                }
            }
        }

        // Profit Sharing 5% (Khusus Platinum - hanya untuk aktivasi perdana)
        // Skip profit sharing untuk pin upgrade (type = 'upgrade')
        if ($pin->type != 'upgrade' && $pin->name == 'Platinum' && $pin->profit_sharing_percent > 0) {
            // Cek apakah ini aktivasi perdana Platinum
            $isPerdana = !$user->profitSharings()->where('is_perdana_platinum', true)->exists();
            if ($isPerdana) {
                ProfitSharing::create([
                    'user_id' => $user->id,
                    'is_perdana_platinum' => true,
                    'date' => date('Y-m-d'),
                ]);
            }
        }

        // Masa Aktif & Maintenance
        // Set masa aktif langsung saat join: Gold 45 hari, Platinum 90 hari
        if (in_array($pin->name, ['Gold', 'Platinum'])) {
            // Gunakan active_days dari pin jika ada, jika tidak gunakan default
            $activeDays = $pin->active_days ?? ($pin->name == 'Gold' ? 45 : 90);
            $user->update([
                'active_until' => Carbon::now()->addDays($activeDays),
                'active_days_initial' => $activeDays,
                'is_active' => true,
            ]);
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

    /**
     * Mencari upline Platinum terdekat di atas user tertentu
     * Digunakan untuk push-up mechanism ketika di bawah Gold ada Platinum
     */
    public static function findPlatinumUpline($user)
    {
        $sponsor = $user->sponsor;
        while ($sponsor) {
            if ($sponsor->premiumUserPin && $sponsor->premiumUserPin->pin) {
                $sponsorPin = $sponsor->premiumUserPin->pin;
                if ($sponsorPin->name == 'Platinum') {
                    return $sponsor;
                }
            }
            $sponsor = $sponsor->sponsor;
        }
        return null;
    }

    /**
     * Mencari upline aktif terdekat di atas user tertentu
     * Digunakan untuk push-up mechanism ketika akun tidak aktif 90 hari
     */
    public static function findActiveUpline($user)
    {
        $sponsor = $user->sponsor;
        while ($sponsor) {
            // Cek apakah sponsor punya pin generasi
            if ($sponsor->premiumUserPin && 
                $sponsor->premiumUserPin->pin &&
                in_array($sponsor->premiumUserPin->pin->name, ['Gold', 'Platinum'])) {
                // Cek apakah sponsor aktif
                $isActive = $sponsor->is_active;
                if ($sponsor->active_until) {
                    // Cek apakah tidak melewati 90 hari dari active_until
                    $inactiveDate = Carbon::parse($sponsor->active_until);
                    $isActive = $isActive && $inactiveDate->addDays(90)->gte(Carbon::now());
                }
                
                if ($isActive) {
                    return $sponsor;
                }
            }
            $sponsor = $sponsor->sponsor;
        }
        return null;
    }

    /**
     * Mencari monoleg (leg kanan) secara recursive untuk bonus monoleg
     * Leg kanan = sponsor yang ditempatkan di sisi kanan
     * Bonus monoleg diberikan ke upline yang memiliki leg kanan
     */
    public static function findMonolegRecursive($sponsor, $currentUser)
    {
        // Cari sponsor langsung yang ditempatkan di kanan
        $rightLeg = $sponsor->sponsors()->where('placement_side', 'right')
            ->whereHas('premiumUserPin')
            ->orderBy('created_at', 'asc')
            ->first();
        
        if ($rightLeg) {
            // Jika current user adalah di bawah right leg, return sponsor (upline dari right leg)
            if ($currentUser->sponsor_id == $rightLeg->id) {
                return $sponsor;
            }
            // Jika current user masih di bawah right leg, lanjutkan recursive
            $found = Helper::findMonolegRecursive($rightLeg, $currentUser);
            if ($found) {
                return $found;
            }
        }
        
        // Jika current user langsung di bawah sponsor dan tidak ada right leg sebelumnya, return sponsor
        if ($currentUser->sponsor_id == $sponsor->id && $currentUser->placement_side == 'right') {
            return $sponsor;
        }
        
        return null;
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
        
        // Perpanjang masa aktif 45 hari dari automaintain RO
        if ($user->active_until) {
            Helper::extendActiveStatus($user, 'automaintain_ro');
        }
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

    /**
     * Hitung Profit Sharing 5% harian untuk Platinum (perdana)
     * Dipanggil setiap hari untuk menghitung akumulasi
     */
    /**
     * Hitung Profit Sharing 5%
     * DIHITUNG HARIAN jika sudah Qualified (hanya untuk Platinum aktivasi perdana)
     * Dipanggil setiap hari untuk menghitung profit sharing
     */
    public static function calculateProfitSharing($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        // Hitung total omzet perusahaan hari ini
        $totalOmzet = Helper::transactionPoin(DateTime::createFromFormat('Y-m-d', $date)) * 1000; // Convert poin ke rupiah (1 poin = 1000)
        $profitSharingAmount = round($totalOmzet * 0.05); // 5% dari omzet
        
        // Dapatkan semua user Platinum yang aktivasi perdana dan sudah Qualified
        $platinumUsers = User::whereHas('profitSharings', function ($q) {
            $q->where('is_perdana_platinum', true);
        })->where('is_active', true)->get();
        
        foreach ($platinumUsers as $user) {
            // Cek apakah user sudah Qualified (minimal 3 tim aktif)
            $activeTeams = $user->sponsors()
                ->whereHas('premiumUserPin')
                ->where('is_active', true)
                ->count();
            
            // Hanya hitung jika sudah Qualified (minimal 3 tim aktif)
            if ($activeTeams >= 3) {
                $profitSharing = $user->profitSharings()->where('is_perdana_platinum', true)->first();
                if ($profitSharing) {
                    $dailyAccumulation = $profitSharing->daily_accumulation + $profitSharingAmount;
                    $walletCashback = min($dailyAccumulation, 22500000); // Maksimal 22.500.000
                    
                    $profitSharing->update([
                        'daily_accumulation' => $dailyAccumulation,
                        'wallet_cashback' => $walletCashback,
                        'date' => $date,
                    ]);
                }
            }
        }
    }

    /**
     * Payout Profit Sharing bulanan
     * Dipanggil setiap bulan untuk membayar profit sharing
     */
    public static function payoutProfitSharing($month)
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        $users = User::whereHas('profitSharings', function ($q) {
            $q->where('is_perdana_platinum', true);
        })->where('is_active', true)->get();
        
        foreach ($users as $user) {
            $profitSharing = $user->profitSharings()->where('is_perdana_platinum', true)->first();
            if ($profitSharing && $profitSharing->wallet_cashback > 0) {
                $user->bonuses()->create([
                    'type' => 'Bonus Profit Sharing',
                    'amount' => $profitSharing->wallet_cashback,
                    'description' => 'Bonus Profit Sharing 5% untuk bulan ' . $month . '.',
                    'created_at' => $month . '-01 00:00:00',
                    'updated_at' => $month . '-01 00:00:00',
                ]);
                
                // Reset wallet cashback setelah payout
                $profitSharing->update([
                    'wallet_cashback' => 0,
                    'monthly_total' => $profitSharing->wallet_cashback,
                ]);
            }
        }
    }

    /**
     * Hitung Bonus Power Plus 8%
     * DIHITUNG BULANAN (bukan harian)
     * Dipanggil setiap bulan untuk menghitung bonus power plus berdasarkan omzet bulanan
     */
    public static function calculatePowerPlus($month = null)
    {
        if (!$month) {
            $month = date('Y-m');
        }
        
        $date = DateTime::createFromFormat('Y-m', $month);
        $startDate = $date->format('Y-m-01');
        $endDate = $date->format('Y-m-t');
        
        // Dapatkan semua user yang memiliki 2 tim aktif (kiri & kanan)
        $qualifiedUsers = User::whereHas('premiumUserPin')
            ->where('is_active', true)
            ->get()
            ->filter(function ($user) {
                $leftTeam = $user->sponsors()->where('placement_side', 'left')
                    ->whereHas('premiumUserPin')
                    ->where('is_active', true)
                    ->count();
                $rightTeam = $user->sponsors()->where('placement_side', 'right')
                    ->whereHas('premiumUserPin')
                    ->where('is_active', true)
                    ->count();
                return $leftTeam >= 1 && $rightTeam >= 1;
            });
        
        foreach ($qualifiedUsers as $user) {
            // Hitung omzet kiri dan kanan untuk bulan tersebut (akumulasi harian)
            $leftOmzet = Helper::calculateLegOmzetMonthly($user, 'left', $month);
            $rightOmzet = Helper::calculateLegOmzetMonthly($user, 'right', $month);
            $smallerLegOmzet = min($leftOmzet, $rightOmzet);
            
            $isQualified15k = $smallerLegOmzet >= 15000;
            $isQualified30k = $smallerLegOmzet >= 30000;
            
            // Simpan dengan tanggal akhir bulan
            PowerPlusQualification::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $endDate,
                ],
                [
                    'left_omzet' => $leftOmzet,
                    'right_omzet' => $rightOmzet,
                    'smaller_leg_omzet' => $smallerLegOmzet,
                    'is_qualified_15k' => $isQualified15k,
                    'is_qualified_30k' => $isQualified30k,
                ]
            );
        }
        
        // Hitung total payout perusahaan bulanan (8% dari total omzet bulanan)
        $totalOmzet = Helper::transactionPoin($date) * 1000;
        $totalPayout = round($totalOmzet * 0.08); // 8% untuk Power Plus
        
        // Distribusi ke qualified members
        $qualified15k = PowerPlusQualification::where('date', $endDate)
            ->where('is_qualified_15k', true)
            ->count();
        $qualified30k = PowerPlusQualification::where('date', $endDate)
            ->where('is_qualified_30k', true)
            ->count();
        
        if ($qualified15k > 0) {
            $bonus15k = round(($totalPayout * 0.04) / $qualified15k); // 4% dibagi jumlah qualified
            PowerPlusQualification::where('date', $endDate)
                ->where('is_qualified_15k', true)
                ->get()
                ->each(function ($qualification) use ($bonus15k, $month) {
                    $qualification->update(['bonus_amount' => $bonus15k]);
                    $qualification->user->bonuses()->create([
                        'type' => 'Bonus Power Plus',
                        'amount' => $bonus15k,
                        'description' => 'Bonus Power Plus untuk omzet kaki kecil 15.000 point bulan ' . $month . '.',
                        'created_at' => $month . '-01 00:00:00',
                        'updated_at' => $month . '-01 00:00:00',
                    ]);
                });
        }
        
        if ($qualified30k > 0) {
            $bonus30k = round(($totalPayout * 0.04) / $qualified30k); // 4% dibagi jumlah qualified
            PowerPlusQualification::where('date', $endDate)
                ->where('is_qualified_30k', true)
                ->get()
                ->each(function ($qualification) use ($bonus30k, $month) {
                    if ($qualification->bonus_amount == 0) {
                        $qualification->update(['bonus_amount' => $bonus30k]);
                        $qualification->user->bonuses()->create([
                            'type' => 'Bonus Power Plus',
                            'amount' => $bonus30k,
                            'description' => 'Bonus Power Plus untuk omzet kaki kecil 30.000 point bulan ' . $month . '.',
                            'created_at' => $month . '-01 00:00:00',
                            'updated_at' => $month . '-01 00:00:00',
                        ]);
                    }
                });
        }
    }

    /**
     * Hitung omzet leg (kiri atau kanan) untuk tanggal tertentu (harian)
     */
    public static function calculateLegOmzet($user, $side, $date)
    {
        $sponsors = $user->sponsors()->where('placement_side', $side)
            ->whereHas('premiumUserPin')
            ->get();
        
        $omzet = 0;
        foreach ($sponsors as $sponsor) {
            // Hitung omzet dari sponsor dan downline-nya
            $dailyPoin = $sponsor->dailyPoins()->where('date', $date)->first();
            if ($dailyPoin) {
                $omzet += $dailyPoin->pp + $dailyPoin->pr;
            }
            // Recursive untuk downline
            $omzet += Helper::calculateLegOmzet($sponsor, $side, $date);
        }
        
        return $omzet;
    }

    /**
     * Hitung omzet leg (kiri atau kanan) untuk bulan tertentu (bulanan - akumulasi)
     */
    public static function calculateLegOmzetMonthly($user, $side, $month)
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        $startDate = $date->format('Y-m-01');
        $endDate = $date->format('Y-m-t');
        
        $sponsors = $user->sponsors()->where('placement_side', $side)
            ->whereHas('premiumUserPin')
            ->get();
        
        $omzet = 0;
        foreach ($sponsors as $sponsor) {
            // Hitung omzet bulanan dari sponsor (akumulasi semua hari dalam bulan)
            $monthlyPoins = $sponsor->dailyPoins()
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
            
            foreach ($monthlyPoins as $dailyPoin) {
                $omzet += $dailyPoin->pp + $dailyPoin->pr;
            }
            
            // Recursive untuk downline
            $omzet += Helper::calculateLegOmzetMonthly($sponsor, $side, $month);
        }
        
        return $omzet;
    }

    /**
     * Hitung Tabungan Umroh/Trip 4%
     * DIHITUNG HARIAN jika sudah Qualified (minimal 3 tim aktif)
     * Masuk ke tabel klaim (umroh_trip_savings)
     * Dipanggil setiap hari
     */
    public static function calculateUmrohTrip($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $year = date('Y', strtotime($date));
        
        // Hitung 4% dari omzet perusahaan
        $totalOmzet = Helper::transactionPoin(DateTime::createFromFormat('Y-m-d', $date)) * 1000;
        $umrohAmount = round($totalOmzet * 0.04);
        
        // Dapatkan semua user yang memiliki minimal 3 tim aktif
        $qualifiedUsers = User::whereHas('premiumUserPin', function ($q) {
            $q->whereHas('pin', function ($qPin) {
                $qPin->whereIn('name', ['Gold', 'Platinum']);
            });
        })
        ->where('is_active', true)
        ->get()
        ->filter(function ($user) {
            $activeTeams = $user->sponsors()
                ->whereHas('premiumUserPin')
                ->where('is_active', true)
                ->count();
            return $activeTeams >= 3;
        });
        
        foreach ($qualifiedUsers as $user) {
            $umrohSaving = UmrohTripSaving::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'year' => $year,
                ],
                [
                    'yearly_accumulation' => 0,
                    'claimed_amount' => 0,
                    'active_teams_count' => $user->sponsors()
                        ->whereHas('premiumUserPin')
                        ->where('is_active', true)
                        ->count(),
                ]
            );
            
            // Tambahkan akumulasi (maksimal 50.000.000 per tahun)
            $newAccumulation = min($umrohSaving->yearly_accumulation + $umrohAmount, 50000000);
            $umrohSaving->update([
                'yearly_accumulation' => $newAccumulation,
            ]);
        }
    }

    /**
     * Perpanjang masa aktif user
     * Dipanggil ketika user melakukan repeat order atau sponsor 2 orang baru
     */
    public static function extendActiveStatus($user, $method = 'repeat_order')
    {
        if (!$user->active_until) {
            return;
        }
        
        // Perpanjang 45 hari
        $newActiveUntil = Carbon::parse($user->active_until)->addDays(45);
        $user->update([
            'active_until' => $newActiveUntil,
            'is_active' => true,
        ]);
    }

    /**
     * Cek dan perpanjang masa aktif berdasarkan belanja RO
     * Jika total belanja RO dalam masa aktif >= 1.7 juta (Gold) atau 12.75 juta (Platinum), perpanjang 45 hari
     * Bisa dari automaintain atau dari belanja RO total dalam kisaran masa aktif
     */
    public static function checkAndExtendActiveFromRO($user, $transactionAmount = 0)
    {
        if (!$user->active_until) {
            return false;
        }

        // Cek apakah user punya pin Gold atau Platinum
        $userPin = $user->premiumUserPin;
        if (!$userPin || !$userPin->pin) {
            return false;
        }

        $pin = $userPin->pin;
        $roPrice = $pin->ro_price ?? ($pin->name == 'Platinum' ? 12750000 : 1700000); // Default 1.7 juta untuk Gold, 12.75 juta untuk Platinum

        // Hitung total belanja RO dalam masa aktif (dari tanggal mulai aktif sampai sekarang)
        $activeFrom = Carbon::parse($user->active_until)->subDays($user->active_days_initial ?? 45);
        
        // Total belanja dari automaintain RO (PIN PAKET RO atau BSM GOLD Automaintain)
        $automaintainRO = $user->userPins()
            ->whereHas('pin', function($q) {
                $q->whereIn('name', ['PIN PAKET RO', 'BSM GOLD Automaintain']);
            })
            ->whereBetween('created_at', [$activeFrom, Carbon::now()])
            ->sum('price');

        // Total belanja RO dari transaksi umum dalam masa aktif
        // Hitung semua transaksi yang sudah paid/received dalam masa aktif
        $transactionRO = Transaction::where('user_id', $user->id)
            ->where('type', 'general')
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->whereBetween('created_at', [$activeFrom, Carbon::now()])
            ->sum('price');

        // Tambahkan transaction amount yang baru saja dibuat (jika ada)
        $transactionRO += $transactionAmount;

        $totalRO = $automaintainRO + $transactionRO;

        // Jika total RO >= harga RO, perpanjang 45 hari
        // Kita perlu track agar tidak double extend - cek apakah sudah pernah extend dalam periode ini
        // Untuk sementara, kita extend jika mencapai threshold
        if ($totalRO >= $roPrice) {
            Helper::extendActiveStatus($user, 'belanja_ro');
            return true;
        }
        
        return false;
    }

    /**
     * Cek dan update status aktif user
     * Dipanggil setiap hari untuk mengecek masa aktif
     */
    public static function checkActiveStatus($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $users = User::whereNotNull('active_until')
            ->where('active_until', '<', $date)
            ->where('is_active', true)
            ->get();
        
        foreach ($users as $user) {
            $user->update(['is_active' => false]);
        }
    }
}