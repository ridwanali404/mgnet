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
use Illuminate\Support\Facades\DB;
use App\Models\GlobalDailyPoin;
use App\Models\OfficialTransaction;
use App\Models\PowerPlusQualification;
use App\Models\ProfitSharing;
use App\Models\ProfitSharingDaily;
use App\Models\UmrohTripSaving;
use App\Models\UmrohTripDaily;
use App\Models\GlobalProfitSharingDaily;
use App\Models\GlobalProfitSharingSaving;
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
                    $action = $pin->type == 'upgrade' ? 'upgrade' : 'join';
                    $bonus = $sponsor->bonuses()->create([
                        'type' => 'Komisi Sponsor',
                        'amount' => $amount,
                        'description' => 'Komisi Sponsor dari ' . $action . ' ' . $user->username . ' paket ' . $pin->name . '.',
                    ]);
                    Helper::automaintain($sponsor, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                }
            }
        } else {
            // Bonus Sponsor 15% - DIBUAT HARIAN (dibayar langsung saat upgrade)
            // Skip bonus sponsor untuk pin upgrade (type = 'upgrade') dan RO (is_ro = true)
            $isRO = $userPin->is_ro ?? false;
            if (!$isRO && $pin->type != 'upgrade') {
                $sponsor = $user->sponsor;
                if ($sponsor && $sponsor->premiumUserPin()->count()) {
                    // Gunakan persentase jika ada, jika tidak gunakan nominal untuk backward compatibility
                    if ($pin->bonus_sponsor_percent > 0) {
                        $amount = round($pin->price * $pin->bonus_sponsor_percent / 100);
                    } else {
                        $amount = $pin->bonus_sponsor;
                    }
                    if ($amount > 0) {
                        $action = $pin->type == 'upgrade' ? 'upgrade' : 'join';
                        $bonus = $sponsor->bonuses()->create([
                            'type' => 'Komisi Sponsor',
                            'amount' => $amount,
                            'description' => 'Komisi Sponsor dari ' . $action . ' ' . $user->username . ' paket ' . $pin->name . '.',
                        ]);
                        Helper::automaintain($sponsor, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                    }
                }
            }
            // Cek apakah ini pin RO (is_ro = true) untuk base pin Gold atau Platinum
            // Bonus Monoleg untuk RO base pin (jika ada bonus_monoleg)
            // Semua Komisi Monoleg dari RO berasal dari Automaintain (ada yang Automaintain di bawahnya)
            // FIX: Semua AUTO RO menggunakan harga Gold (1.7 juta) bukan harga pin asli
            $isRO = $userPin->is_ro ?? false;
            if ($isRO && in_array($pin->name, ['Gold', 'Platinum']) && $pin->monoleg_percent > 0) {
                $sponsor = $user->sponsor;
                // Syarat: sponsor harus memiliki minimal 1 downline langsung
                if ($sponsor && $sponsor->uplines()->whereHas('premiumUserPin')->count() >= 1) {
                    // FIX: Gunakan harga Gold untuk semua AUTO RO (1.7 juta)
                    $goldPin = Pin::where('name', 'Gold')->where('type', 'premium')->first();
                    $roPrice = $goldPin->ro_price ?? 1700000; // Default 1.7 juta untuk Gold
                    $monolegPercent = $goldPin->monoleg_percent ?? 9; // Default 9% untuk Gold
                    
                    // Hitung bonus monoleg secara recursive untuk semua level (level 1, 2, 3, dst)
                    Helper::calculateMonolegBonusRecursive($sponsor, $user, $roPrice, $monolegPercent, 'RO Automaintain');
                }
            }
        }

        // Bonus Generasi 19% - DIBUAT HARIAN (dibayar langsung saat upgrade)
        // Untuk RO (is_ro = true), bonus generasi dibuat sebagai POTENSI seperti join Gold (tanpa sponsor)
        // Potensi ini akan divalidasi saat monthly closing apakah user qualified (170 PV atau automaintain)
        $isRO = $userPin->is_ro ?? false;
        $sponsor = $user->sponsor;
        
        // Tentukan sumber RO (AUTO RO atau AUTOMAINTAIN)
        $roSource = null;
        if ($isRO) {
            // Cek apakah ada automaintain record yang terkait dengan RO ini
            $automaintain = $user->automaintains()
                ->where('type', 'D')
                ->where('amount', 1700000)
                ->whereBetween('created_at', [
                    Carbon::parse($userPin->created_at)->subMinutes(5),
                    Carbon::parse($userPin->created_at)->addMinutes(5)
                ])
                ->first();
            
            if ($automaintain) {
                $roSource = 'AUTOMAINTAIN';
            } else {
                $roSource = 'AUTORO';
            }
        }
        
        // Untuk RO, gunakan paket Gold sebagai acuan (seperti join Gold tanpa sponsor)
        if ($isRO) {
            $targetPin = Pin::where('name', 'Gold')->where('type', 'premium')->first();
        } else {
            // Untuk upgrade Gold ke Platinum, hitung bonus generasi berdasarkan paket Platinum
            $targetPin = $pin;
            if ($pin->type == 'upgrade' && str_contains($pin->name, 'Platinum')) {
                // Cari paket Platinum untuk menghitung bonus generasi
                $targetPin = Pin::where('name', 'Platinum')->where('type', 'premium')->first();
            }
        }
        
        if ($targetPin && $targetPin->is_generasi && $targetPin->price && $targetPin->generasi_percent > 0) {
            // Hitung total alokasi bonus generasi (19% dari harga paket)
            $totalAllocation = round($targetPin->price * $targetPin->generasi_percent / 100);
            
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
                    // Juga berlaku untuk upgrade Gold ke Platinum
                    if ($sponsorPin->name == 'Gold' && ($pin->name == 'Platinum' || ($pin->type == 'upgrade' && str_contains($pin->name, 'Platinum')))) {
                        // Cari upline Platinum terdekat di atas Gold ini
                        $platinumUpline = Helper::findPlatinumUpline($sponsor);
                        if ($platinumUpline) {
                            // Hitung bonus Gold yang seharusnya didapat oleh Gold sponsor
                            $goldPin = Pin::where('name', 'Gold')->first();
                            if ($goldPin && $goldPin->price && $goldPin->generasi_percent > 0) {
                                $goldAllocation = round($goldPin->price * $goldPin->generasi_percent / 100);
                                $percent = $generasiPercentages[$i - 1] ?? 0;
                                $goldAmount = round($goldAllocation * $percent / 100);
                                
                                // Berikan bonus Gold ke Gold sponsor
                                if ($goldAmount > 0) {
                                    if ($isRO) {
                                        $action = 'RO';
                                        // Jika ada roSource (AUTORO atau AUTOMAINTAIN), hilangkan "paket Gold (RO)"
                                        if ($roSource) {
                                            $pinName = '';
                                            $sourceText = ' (' . $roSource . ')';
                                        } else {
                                            $pinName = 'Gold (RO)';
                                            $sourceText = '';
                                        }
                                    } else {
                                        $pinName = ($pin->type == 'upgrade' && str_contains($pin->name, 'Platinum')) ? 'Platinum' : $pin->name;
                                        $action = $pin->type == 'upgrade' ? 'upgrade' : 'join';
                                        $sourceText = '';
                                    }
                                    $description = 'Bonus Generasi dari ' . $action . ' ' . $user->username;
                                    if ($pinName) {
                                        $description .= ' paket ' . $pinName;
                                    }
                                    $description .= $sourceText . '. Generasi ke-' . $i . ' sebesar ' . $percent . '% dari alokasi (Rp ' . number_format($goldAllocation, 0, ',', '.') . ').';
                                    $goldBonus = $sponsor->bonuses()->create([
                                        'type' => 'Bonus Generasi',
                                        'amount' => $goldAmount,
                                        'description' => $description,
                                    ]);
                                    Helper::automaintain($sponsor, 'K', $goldBonus->amount, 'Saldo automaintain dari ' . $goldBonus->description);
                                }
                                
                                // Hitung bonus Platinum dan selisihnya
                                $platinumAmount = round($totalAllocation * $percent / 100);
                                $differenceAmount = $platinumAmount - $goldAmount;
                                
                                // Berikan selisih ke Platinum upline
                                if ($differenceAmount > 0) {
                                    if ($isRO) {
                                        $action = 'RO';
                                        // Jika ada roSource (AUTORO atau AUTOMAINTAIN), hilangkan "paket Gold (RO)"
                                        if ($roSource) {
                                            $pinName = '';
                                            $sourceText = ' (' . $roSource . ')';
                                        } else {
                                            $pinName = 'Gold (RO)';
                                            $sourceText = '';
                                        }
                                    } else {
                                        $pinName = ($pin->type == 'upgrade' && str_contains($pin->name, 'Platinum')) ? 'Platinum' : $pin->name;
                                        $action = $pin->type == 'upgrade' ? 'upgrade' : 'join';
                                        $sourceText = '';
                                    }
                                    $description = 'Bonus Generasi dari ' . $action . ' ' . $user->username;
                                    if ($pinName) {
                                        $description .= ' paket ' . $pinName;
                                    }
                                    $description .= $sourceText . '. Generasi ke-' . $i . ' (Push-up dari ' . $sponsor->username . ' Gold) sebesar ' . $percent . '% dari alokasi (Rp ' . number_format($totalAllocation, 0, ',', '.') . ').';
                                    $bonus = $platinumUpline->bonuses()->create([
                                        'type' => 'Bonus Generasi',
                                        'amount' => $differenceAmount,
                                        'description' => $description,
                                    ]);
                                    Helper::automaintain($platinumUpline, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                                }
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
                                if ($isRO) {
                                    $action = 'RO';
                                    // Jika ada roSource (AUTORO atau AUTOMAINTAIN), hilangkan "paket Gold (RO)"
                                    if ($roSource) {
                                        $pinName = '';
                                        $sourceText = ' (' . $roSource . ')';
                                    } else {
                                        $pinName = 'Gold (RO)';
                                        $sourceText = '';
                                    }
                                } else {
                                    $action = $pin->type == 'upgrade' ? 'upgrade' : 'join';
                                    $pinName = $pin->name;
                                    $sourceText = '';
                                }
                                $description = 'Bonus Generasi dari ' . $action . ' ' . $user->username;
                                if ($pinName) {
                                    $description .= ' paket ' . $pinName;
                                }
                                $description .= $sourceText . '. Generasi ke-' . $i . ' (Push-up dari ' . $sponsor->username . ' tidak aktif 90 hari) sebesar ' . $percent . '% dari alokasi (Rp ' . number_format($totalAllocation, 0, ',', '.') . ').';
                                $bonus = $activeUpline->bonuses()->create([
                                    'type' => 'Bonus Generasi',
                                    'amount' => $amount,
                                    'description' => $description,
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
                            if ($isRO) {
                                $action = 'RO';
                                // Jika AUTORO, hilangkan "paket Gold (RO)"
                                if ($roSource == 'AUTORO') {
                                    $pinName = '';
                                    $sourceText = ' (' . $roSource . ')';
                                } else {
                                    $pinName = 'Gold (RO)';
                                    $sourceText = $roSource ? ' (' . $roSource . ')' : '';
                                }
                            } else {
                                $pinName = ($pin->type == 'upgrade' && str_contains($pin->name, 'Platinum')) ? 'Platinum' : $pin->name;
                                $action = $pin->type == 'upgrade' ? 'upgrade' : 'join';
                                $sourceText = '';
                            }
                            $description = 'Bonus Generasi dari ' . $action . ' ' . $user->username;
                            if ($pinName) {
                                $description .= ' paket ' . $pinName;
                            }
                            $description .= $sourceText . '. Generasi ke-' . $i . ' sebesar ' . $percent . '% dari alokasi (Rp ' . number_format($totalAllocation, 0, ',', '.') . ').';
                            $bonus = $sponsor->bonuses()->create([
                                'type' => 'Bonus Generasi',
                                'amount' => $amount,
                                'description' => $description,
                            ]);
                            Helper::automaintain($sponsor, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                        }
                    }
                }
                
                $sponsor = $sponsor->sponsor;
            }
        }

        // Bonus Monoleg 9% untuk Gold & Platinum (bukan BSM dan bukan RO) - DIBUAT HARIAN (dibayar langsung saat upgrade)
        // FIX: Hitung bonus monoleg secara recursive untuk semua level (level 1, 2, 3, dst)
        // $isRO sudah didefinisikan di atas
        if (!$isRO && !str_contains($pin->name, 'BSM') && in_array($pin->name, ['Gold', 'Platinum']) && $pin->monoleg_percent > 0) {
            $sponsor = $user->sponsor;
            // Syarat: sponsor harus memiliki minimal 1 downline langsung (berdasarkan upline_id)
            if ($sponsor && $sponsor->uplines()->whereHas('premiumUserPin')->count() >= 1) {
                $action = $pin->type == 'upgrade' ? 'upgrade' : 'join';
                $description = 'Bonus Monoleg 9% dari ' . $action . ' ' . $user->username . ' paket ' . $pin->name . '.';
                // Hitung bonus monoleg secara recursive untuk semua level
                Helper::calculateMonolegBonusRecursive($sponsor, $user, $pin->price, $pin->monoleg_percent, $description);
            }
        }

        // Profit Sharing 5% (Khusus Platinum - hanya untuk aktivasi perdana)
        // Skip profit sharing untuk pin upgrade (type = 'upgrade') dan RO (is_ro = true)
        $isRO = $userPin->is_ro ?? false;
        if (!$isRO && $pin->type != 'upgrade' && $pin->name == 'Platinum' && $pin->profit_sharing_percent > 0) {
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
        // Hanya untuk join/upgrade normal, bukan RO (RO sudah diperpanjang di Helper::ro())
        $isRO = $userPin->is_ro ?? false;
        if (!$isRO && in_array($pin->name, ['Gold', 'Platinum'])) {
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
     * Berikan bonus generasi seperti join Gold TANPA bonus sponsor
     * Digunakan untuk Bonus RO/Automaintain
     */
    public static function giveROBonusGenerasi($user, $month)
    {
        // Gunakan paket Gold sebagai acuan untuk bonus RO
        $goldPin = Pin::where('name', 'Gold')->where('type', 'premium')->first();
        if (!$goldPin || !$goldPin->is_generasi || !$goldPin->price || !$goldPin->generasi_percent) {
            return;
        }
        
        // Hitung total alokasi bonus generasi (19% dari harga paket Gold)
        $totalAllocation = round($goldPin->price * $goldPin->generasi_percent / 100);
        
        // Distribusi persentase per generasi: 25%, kemudian turun sampai 3%
        // Generasi 1: 25%, 2: 20%, 3: 15%, 4: 12%, 5: 10%, 6: 8%, 7: 6%, 8: 5%, 9: 4%, 10: 3%
        $generasiPercentages = [25, 20, 15, 12, 10, 8, 6, 5, 4, 3];
        
        // Menggunakan upline_id untuk tree bonus (kecuali bonus sponsor dan generasi)
        $sponsor = $user->upline;
        
        for ($i = 1; $i <= 10; $i++) {
            if (!$sponsor) {
                break;
            }
            
            // Cek apakah sponsor punya pin generasi (Gold atau Platinum)
            if ($sponsor->premiumUserPin && $sponsor->premiumUserPin->pin) {
                $sponsorPin = $sponsor->premiumUserPin->pin;
                
                // Push-up mechanism: Jika di bawah Gold terdapat Platinum, selisih naik ke upline Platinum
                if ($sponsorPin->name == 'Gold') {
                    // Cari upline Platinum terdekat di atas Gold ini
                    $platinumUpline = Helper::findPlatinumUpline($sponsor);
                    if ($platinumUpline) {
                        // Hitung bonus Gold yang seharusnya didapat oleh Gold sponsor
                        $percent = $generasiPercentages[$i - 1] ?? 0;
                        $goldAmount = round($totalAllocation * $percent / 100);
                        
                        // Berikan bonus Gold ke Gold sponsor
                        if ($goldAmount > 0) {
                            $goldBonus = $sponsor->bonuses()->create([
                                'type' => 'Bonus Generasi',
                                'amount' => $goldAmount,
                                'description' => 'Bonus Generasi dari RO ' . $user->username . '. Generasi ke-' . $i . ' sebesar ' . $percent . '% dari alokasi (Rp ' . number_format($totalAllocation, 0, ',', '.') . ').',
                                'created_at' => $month . '-01 00:00:00',
                                'updated_at' => $month . '-01 00:00:00',
                            ]);
                            Helper::automaintain($sponsor, 'K', $goldBonus->amount, 'Saldo automaintain dari ' . $goldBonus->description);
                        }
                        
                        // Hitung bonus Platinum dan selisihnya
                        $platinumPin = Pin::where('name', 'Platinum')->where('type', 'premium')->first();
                        if ($platinumPin && $platinumPin->is_generasi && $platinumPin->price && $platinumPin->generasi_percent > 0) {
                            $platinumAllocation = round($platinumPin->price * $platinumPin->generasi_percent / 100);
                            $platinumAmount = round($platinumAllocation * $percent / 100);
                            $differenceAmount = $platinumAmount - $goldAmount;
                            
                            // Berikan selisih ke Platinum upline
                            if ($differenceAmount > 0) {
                                $bonus = $platinumUpline->bonuses()->create([
                                    'type' => 'Bonus Generasi',
                                    'amount' => $differenceAmount,
                                    'description' => 'Bonus Generasi dari RO ' . $user->username . '. Generasi ke-' . $i . ' (Push-up dari ' . $sponsor->username . ' Gold) sebesar ' . $percent . '% dari alokasi (Rp ' . number_format($platinumAllocation, 0, ',', '.') . ').',
                                    'created_at' => $month . '-01 00:00:00',
                                    'updated_at' => $month . '-01 00:00:00',
                                ]);
                                Helper::automaintain($platinumUpline, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                            }
                        }
                        // Skip Gold ini, lanjut ke sponsor berikutnya
                        $sponsor = $sponsor->upline;
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
                                'description' => 'Bonus Generasi dari RO ' . $user->username . '. Generasi ke-' . $i . ' (Push-up dari ' . $sponsor->username . ' tidak aktif 90 hari) sebesar ' . $percent . '% dari alokasi (Rp ' . number_format($totalAllocation, 0, ',', '.') . ').',
                                'created_at' => $month . '-01 00:00:00',
                                'updated_at' => $month . '-01 00:00:00',
                            ]);
                            Helper::automaintain($activeUpline, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                        }
                        // Skip sponsor tidak aktif, lanjut ke sponsor berikutnya
                        $sponsor = $sponsor->upline;
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
                            'description' => 'Bonus Generasi dari RO ' . $user->username . '. Generasi ke-' . $i . ' sebesar ' . $percent . '% dari alokasi (Rp ' . number_format($totalAllocation, 0, ',', '.') . ').',
                            'created_at' => $month . '-01 00:00:00',
                            'updated_at' => $month . '-01 00:00:00',
                        ]);
                        Helper::automaintain($sponsor, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                    }
                }
            }
            
            // Menggunakan upline_id untuk tree bonus (kecuali bonus sponsor dan generasi)
            if ($sponsor) {
                $sponsor = $sponsor->upline;
            } else {
                break;
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
     * Mencari monoleg (jalur monoleg) secara recursive untuk bonus monoleg
     * Menggunakan uplines (berdasarkan upline_id) bukan sponsors (berdasarkan sponsor_id)
     * Jalur monoleg = downline kedua dan seterusnya (bukan downline pertama/Leg Kiri)
     * Bonus monoleg diberikan ke upline yang memiliki jalur monoleg
     * Leg Kiri tidak dihitung sebagai monoleg, hanya Leg 1, Leg 2, dst
     * 
     * Catatan: Bonus monoleg hanya untuk downline langsung dari jalur monoleg,
     * bukan untuk downline dari downline tersebut (hanya 1 level)
     */
    public static function findMonolegRecursive($sponsor, $currentUser)
    {
        // Ambil semua downline langsung berdasarkan upline_id yang punya premium pin, urutkan berdasarkan created_at
        $allUplines = $sponsor->uplines()
            ->whereHas('premiumUserPin')
            ->orderBy('created_at', 'asc')
            ->get();
        
        if ($allUplines->count() < 2) {
            // Sponsor harus punya minimal 2 downline langsung untuk memiliki jalur monoleg
            return null;
        }
        
        // Downline pertama = Leg Kiri (tidak dihitung sebagai monoleg, tidak dapat bonus)
        // Downline kedua dan seterusnya = jalur monoleg (Leg 1, Leg 2, dst) - dapat bonus
        $monolegUplines = $allUplines->skip(1)->values();
        
        // Cek apakah current user adalah downline langsung dari sponsor di jalur monoleg
        // Bonus monoleg hanya untuk downline langsung dari sponsor, bukan downline dari downline
        foreach ($monolegUplines as $monolegUpline) {
            // Jika current user langsung di bawah sponsor di jalur monoleg (upline_id = sponsor->id), return sponsor
            // Catatan: monolegUpline adalah downline langsung dari sponsor di jalur monoleg
            // Jadi kita cek apakah currentUser adalah monolegUpline itu sendiri (downline langsung dari sponsor)
            if ($currentUser->id == $monolegUpline->id) {
                return $sponsor;
            }
        }
        
        return null;
    }

    /**
     * Hitung bonus monoleg secara recursive untuk semua level (level 1, 2, 3, dst)
     * Bonus monoleg diberikan ke semua upline di jalur monoleg yang memenuhi syarat
     * 
     * @param User $sponsor Sponsor dari user yang melakukan upgrade/RO
     * @param User $currentUser User yang melakukan upgrade/RO
     * @param int $basePrice Harga dasar untuk perhitungan bonus (price untuk join/upgrade, ro_price untuk RO)
     * @param float $monolegPercent Persentase bonus monoleg (biasanya 9%)
     * @param string $descriptionTemplate Template deskripsi bonus (akan ditambahkan level)
     */
    public static function calculateMonolegBonusRecursive($sponsor, $currentUser, $basePrice, $monolegPercent, $descriptionTemplate)
    {
        if (!$sponsor || !$currentUser) {
            return;
        }

        $level = 1;
        $currentSponsor = $sponsor;
        $currentUserForMonoleg = $currentUser;
        
        // Loop untuk mencari semua monoleg di jalur ke atas (level 1, 2, 3, dst)
        while ($currentSponsor) {
            // Cek apakah currentSponsor memiliki minimal 1 downline langsung
            if ($currentSponsor->uplines()->whereHas('premiumUserPin')->count() >= 1) {
                // Cari monoleg di level ini
                $monoleg = Helper::findMonolegRecursive($currentSponsor, $currentUserForMonoleg);
                
                if ($monoleg && $monoleg->premiumUserPin) {
                    // Hitung amount bonus
                    $amount = round($basePrice * $monolegPercent / 100);
                    
                    if ($amount > 0) {
                        // Buat deskripsi dengan level
                        $description = $descriptionTemplate;
                        if ($level > 1) {
                            $description .= ' (Level ' . $level . ')';
                        }
                        
                        // Buat bonus
                        $bonus = $monoleg->bonuses()->create([
                            'type' => 'Komisi Monoleg',
                            'amount' => $amount,
                            'description' => $description,
                        ]);
                        
                        Helper::automaintain($monoleg, 'K', $bonus->amount, 'Saldo automaintain dari ' . $bonus->description);
                    }
                    
                    // Lanjutkan ke level berikutnya
                    // Untuk level berikutnya, kita perlu mencari monoleg dari monoleg yang baru saja mendapat bonus
                    // Current user untuk level berikutnya adalah monoleg yang baru saja mendapat bonus
                    // Current sponsor untuk level berikutnya adalah sponsor dari monoleg tersebut
                    // Catatan: Untuk level 2+, kita mencari monoleg dari monoleg level sebelumnya
                    $currentUserForMonoleg = $monoleg;
                    $currentSponsor = $monoleg->sponsor;
                    $level++;
                } else {
                    // Tidak ada monoleg lagi, stop
                    break;
                }
            } else {
                // Sponsor tidak memenuhi syarat, stop
                break;
            }
            
            // Safety check: maksimal 10 level untuk mencegah infinite loop
            if ($level > 10) {
                break;
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
        $year = $date->format('Y');
        $month = $date->format('m');
        
        // Hitung dari penggunaan PIN (pembelian PIN bulanan)
        // Termasuk: Semua PIN terjual, Upgrade, dan Automaintain/autoRO
        // FIX: Untuk AUTO RO (is_ro = true), gunakan ro_price (1.7 juta) bukan price penuh
        // Automaintain sudah membuat UserPin, jadi semua UserPin yang dibuat harus dihitung
        // Convert price (rupiah) ke poin (1 poin = 1000 rupiah)
        $pinOmzet = UserPin::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->with('pin')
            ->get()
            ->sum(function ($userPin) {
                // Jika ini AUTO RO, gunakan ro_price atau default 1.7 juta
                if ($userPin->is_ro) {
                    $roPrice = $userPin->pin->ro_price ?? 1700000;
                    return $roPrice;
                }
                // Untuk pin normal, gunakan price
                return $userPin->price;
            });
        $pinPoin = $pinOmzet / 1000;
        
        if (KeyValue::where('key', 'poin')->value('value') == 'enable') {
            $poin = Poin::whereYear('date', $year)->whereMonth('date', $month)->first();
            if ($poin) {
                // Tambahkan PIN purchases ke poin
                return $poin->poin + $pinPoin;
            }
        }
        $t = Transaction::whereYear('created_at', $year)->whereMonth('created_at', $month)
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->sum('poin');
        $ot = OfficialTransaction::whereYear('created_at', $year)->whereMonth('created_at', $month)
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->sum('poin');
        $gdp = GlobalDailyPoin::whereYear('date', $year)->whereMonth('date', $month)
            ->sum('pv');
        return $t + $ot + $gdp + $pinPoin;
    }

    /**
     * Hitung total poin transaksi harian (untuk omset harian)
     * @param DateTime|string $date Tanggal dalam format Y-m-d atau DateTime object
     * @return int Total poin untuk hari tersebut
     */
    public static function transactionPoinDaily($date)
    {
        // Convert string to DateTime if needed
        if (is_string($date)) {
            $dateObj = DateTime::createFromFormat('Y-m-d', $date);
            if (!$dateObj) {
                return 0;
            }
        } else {
            $dateObj = $date;
        }
        
        $dateStr = $dateObj->format('Y-m-d');
        
        // Hitung dari penggunaan PIN (pembelian PIN harian)
        // Termasuk: Semua PIN terjual, Upgrade, dan Automaintain/autoRO
        // FIX: Untuk AUTO RO (is_ro = true), gunakan ro_price (1.7 juta) bukan price penuh
        // Automaintain sudah membuat UserPin, jadi semua UserPin yang dibuat harus dihitung
        // Convert price (rupiah) ke poin (1 poin = 1000 rupiah)
        $pinOmzet = UserPin::whereDate('created_at', $dateStr)
            ->get()
            ->sum(function ($userPin) {
                // Jika ini AUTO RO, gunakan ro_price atau default 1.7 juta
                if ($userPin->is_ro) {
                    $roPrice = $userPin->pin->ro_price ?? 1700000;
                    return $roPrice;
                }
                // Untuk pin normal, gunakan price
                return $userPin->price;
            });
        $pinPoin = $pinOmzet / 1000;
        
        // Cek apakah menggunakan Poin model
        if (KeyValue::where('key', 'poin')->value('value') == 'enable') {
            $poin = Poin::whereDate('date', $dateStr)->first();
            if ($poin) {
                // Tambahkan PIN purchases ke poin
                return $poin->poin + $pinPoin;
            }
        }
        
        // Hitung dari Transaction (harian)
        $t = Transaction::whereDate('created_at', $dateStr)
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->sum('poin');
        
        // Hitung dari OfficialTransaction (harian)
        $ot = OfficialTransaction::whereDate('created_at', $dateStr)
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->sum('poin');
        
        // Hitung dari GlobalDailyPoin (harian)
        $gdp = GlobalDailyPoin::whereDate('date', $dateStr)
            ->sum('pv');
        
        return $t + $ot + $gdp + $pinPoin;
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
                ->whereHas('dailyPoinUplines', function ($q) use ($date) {
                    $q->where('date', $date);
                })
                ->get();
            foreach ($qualifiedUsers as $user) {
                // pair bonus - menggunakan upline_id untuk tree bonus (kecuali bonus sponsor dan generasi)
                $pp_dailyPoinSponsors = $user->dailyPoinUplines()->where('date', $date)->orderBy('pp', 'desc')->get();
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

                // reward - menggunakan upline_id untuk tree bonus (kecuali bonus sponsor dan generasi)
                $pr_dailyPoinSponsors = $user->dailyPoinUplines()->where('date', $date)->orderBy('pr', 'desc')->get();
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
        // Batas automaintain adalah 1,7 juta (bukan 2 juta)
        if ($user->cash_automaintain >= 1700000) {
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
        // Gunakan base pin user (Gold, Gold Upgrade Platinum, atau Platinum) untuk RO
        $currentUserPin = $user->premiumUserPin;
        if (!$currentUserPin || !$currentUserPin->pin) {
            return;
        }
        
        $basePin = $currentUserPin->pin;
        
        // Hanya untuk base pin: Gold, Gold Upgrade Platinum, atau Platinum
        if (!in_array($basePin->name, ['Gold', 'Gold Upgrade Platinum', 'Platinum'])) {
            return;
        }
        
        // Automaintain limit adalah 1,7 juta (bukan 2 juta)
        $user->decrement('cash_automaintain', 1700000);
        $user->automaintains()->create([
            'type' => 'D',
            'amount' => 1700000,
            'current' => $user->cash_automaintain,
            'description' => 'Penggunaan Repeat Order ' . $basePin->name . '.',
        ]);
        
        // Buat UserPin dengan base pin yang sama, tapi ditandai sebagai RO
        $userPin = $user->userPins()->create([
            'buyer_id' => $user->id,
            'pin_id' => $basePin->id,
            'code' => strtoupper(str_random(6)),
            'name' => $basePin->name,
            'price' => $basePin->ro_price ?? ($basePin->name == 'Platinum' ? 12750000 : 1700000), // Gunakan harga RO
            'level' => $basePin->level,
            'is_used' => true,
            'is_ro' => true, // Tandai sebagai Repeat Order
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
        
        // Hitung total omzet perusahaan hari ini menggunakan transactionPoinDaily
        $totalOmzet = Helper::transactionPoinDaily($date) * 1000; // Convert poin ke rupiah (1 poin = 1000)
        $profitSharingAmount = round($totalOmzet * 0.05); // 5% dari omzet
        
        // Dapatkan semua user Platinum yang aktivasi perdana (JOIN dari awal, bukan upgrade) dan sudah Qualified
        $platinumUsers = User::whereHas('profitSharings', function ($q) {
            $q->where('is_perdana_platinum', true);
        })
        ->whereHas('premiumUserPin', function ($q) {
            $q->whereHas('pin', function ($qPin) {
                // Hanya Platinum yang JOIN dari awal (type = 'premium', bukan 'upgrade')
                $qPin->where('name', 'Platinum')->where('type', 'premium');
            });
        })
        ->where('is_active', true)
        ->get();
        
        foreach ($platinumUsers as $user) {
            // Cek apakah user sudah Qualified (minimal 3 tim aktif)
            // Menggunakan upline_id untuk tree bonus (kecuali bonus sponsor dan generasi)
            $activeTeams = $user->uplines()
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
                    
                    // Simpan riwayat harian profit sharing
                    \App\Models\ProfitSharingDaily::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'date' => $date,
                        ],
                        [
                            'amount' => $profitSharingAmount,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Payout Profit Sharing bulanan
     * DEPRECATED: Fungsi ini sudah tidak digunakan lagi
     * Diganti dengan payoutGlobalProfitSharing() untuk GPS
     * 
     * @deprecated Gunakan payoutGlobalProfitSharing() sebagai gantinya
     */
    public static function payoutProfitSharing($month)
    {
        // Fungsi ini sudah tidak digunakan lagi
        // Bonus Profit Sharing lama sudah diganti dengan Bonus Global Profit Sharing
        // Tidak membuat bonus lagi untuk menghindari duplikasi
        return;
    }

    /**
     * Payout Global Profit Sharing (GPS) bulanan
     * Menyatukan hasil total harian akumulasi GPS & hasil Powerleg ke bonus bulanan
     * Dipanggil setiap bulan untuk membayar GPS
     * Hanya untuk Platinum perdana aktif - GPS dan Power Plus digabungkan menjadi satu bonus
     */
    public static function payoutGlobalProfitSharing($month)
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        $startDate = $date->format('Y-m-01');
        $endDate = $date->format('Y-m-t');
        
        // Dapatkan semua Platinum perdana aktif
        $platinumUsers = User::whereHas('profitSharings', function ($q) {
            $q->where('is_perdana_platinum', true);
        })
        ->whereHas('premiumUserPin', function ($q) {
            $q->whereHas('pin', function ($qPin) {
                $qPin->where('name', 'Platinum')->where('type', 'premium');
            });
        })
        ->where('is_active', true)
        ->get();
        
        foreach ($platinumUsers as $user) {
            // Ambil saving record
            $gpsSaving = GlobalProfitSharingSaving::where('user_id', $user->id)->first();
            
            // Buat bonus GPS terpisah jika ada wallet_cashback
            if ($gpsSaving && $gpsSaving->wallet_cashback > 0) {
                $user->bonuses()->create([
                    'type' => 'Bonus Global Profit Sharing',
                    'amount' => $gpsSaving->wallet_cashback,
                    'description' => 'Global Profit Sharing 5% untuk bulan ' . $month . '.',
                    'created_at' => $month . '-01 00:00:00',
                    'updated_at' => $month . '-01 00:00:00',
                ]);
                
                // Reset wallet cashback setelah payout
                $gpsSaving->update([
                    'wallet_cashback' => 0,
                    'daily_accumulation' => 0,
                ]);
                Helper::rank($user, $gpsSaving->wallet_cashback);
            }
            
            // Power Plus tetap dibuat terpisah (tidak dihapus atau digabung)
            // Power Plus bonus sudah dibuat oleh calculatePowerPlus() sebelumnya
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
        
        // Dapatkan semua user premium aktif yang memiliki minimal 2 uplines premium
        // Syarat: minimal 2 grup dengan omset tertentu (tidak perlu left/right team)
        $qualifiedUsers = User::whereHas('premiumUserPin')
            ->where('is_active', true)
            ->get()
            ->filter(function ($user) {
                // Minimal 2 uplines premium aktif (untuk bisa punya minimal 2 grup)
                $premiumUplines = $user->uplines()
                    ->whereHas('premiumUserPin')
                    ->where('is_active', true)
                    ->count();
                return $premiumUplines >= 2;
            });
        
        foreach ($qualifiedUsers as $user) {
            // Hitung omset per leg untuk bulan tersebut (akumulasi harian)
            $legOmzets = Helper::calculateAllLegOmzetMonthly($user, $month);
            
            // Untuk backward compatibility, tetap simpan left_omzet dan right_omzet
            // Ambil Leg 1 dan Leg 2 jika ada
            $leftOmzet = $legOmzets['Leg 1'] ?? 0;
            $rightOmzet = isset($legOmzets['Leg 2']) ? $legOmzets['Leg 2'] : 0;
            $smallerLegOmzet = min($leftOmzet, $rightOmzet);
            
            // Logika qualification baru: minimal 2 grup dengan 15k-30k dan minimal 2 grup >=30k
            // Bisa qualified di 2 kelompok sekaligus
            $qualified15kGroups = 0; // Grup dengan omset 15.000 - 29.999
            $qualified30kGroups = 0; // Grup dengan omset >= 30.000
            
            foreach ($legOmzets as $legName => $omzet) {
                if ($omzet >= 30000) {
                    $qualified30kGroups++;
                } elseif ($omzet >= 15000) {
                    $qualified15kGroups++;
                }
            }
            
            $isQualified15k = $qualified15kGroups >= 2; // Minimal 2 grup dengan 15k-30k
            $isQualified30k = $qualified30kGroups >= 2; // Minimal 2 grup dengan >=30k
            
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
                    'leg_omzets' => json_encode($legOmzets),
                    'is_qualified_15k' => $isQualified15k,
                    'is_qualified_30k' => $isQualified30k,
                ]
            );
        }
        
        // Hitung total omset nasional bulanan (Total penjualan Pin sebulanan)
        $totalOmzet = Helper::transactionPoin($date) * 1000; // Convert poin ke rupiah (1 poin = 1000)
        
        // Distribusi ke qualified members
        // Grade 1: 4% dari omset nasional dibagi mitra yang qualified di kelompok 15k-30k
        // Grade 2: 4% dari omset nasional dibagi mitra yang qualified di kelompok >=30k
        $qualified15k = PowerPlusQualification::where('date', $endDate)
            ->where('is_qualified_15k', true)
            ->count();
        $qualified30k = PowerPlusQualification::where('date', $endDate)
            ->where('is_qualified_30k', true)
            ->count();
        
        if ($qualified15k > 0) {
            $bonus15k = round(($totalOmzet * 0.04) / $qualified15k); // 4% dari omset nasional dibagi jumlah qualified Grade 1
            PowerPlusQualification::where('date', $endDate)
                ->where('is_qualified_15k', true)
                ->get()
                ->each(function ($qualification) use ($bonus15k, $month, $endDate) {
                    // Cek apakah bonus 15k sudah pernah dibuat untuk bulan ini
                    $existingBonus15k = $qualification->user->bonuses()
                        ->whereYear('created_at', date('Y', strtotime($month)))
                        ->whereMonth('created_at', date('m', strtotime($month)))
                        ->where('type', 'Bonus Power Plus')
                        ->where('description', 'like', '%15.000 point%')
                        ->exists();
                    
                    if (!$existingBonus15k) {
                        // Update bonus_amount: set ke bonus15k (akan ditambah bonus30k nanti jika qualified)
                        $qualification->update(['bonus_amount' => $bonus15k]);
                        
                        // Buat bonus baru untuk Grade 1
                        $qualification->user->bonuses()->create([
                            'type' => 'Bonus Power Plus',
                            'amount' => $bonus15k,
                            'description' => 'Bonus Power Plus untuk omzet kaki kecil 15.000 point bulan ' . $month . '.',
                            'created_at' => $month . '-01 00:00:00',
                            'updated_at' => $month . '-01 00:00:00',
                        ]);
                        Helper::rank($qualification->user, $bonus15k);
                    }
                });
        }
        
        if ($qualified30k > 0) {
            $bonus30k = round(($totalOmzet * 0.04) / $qualified30k); // 4% dari omset nasional dibagi jumlah qualified Grade 2
            PowerPlusQualification::where('date', $endDate)
                ->where('is_qualified_30k', true)
                ->get()
                ->each(function ($qualification) use ($bonus30k, $month, $endDate) {
                    // Cek apakah bonus 30k sudah pernah dibuat untuk bulan ini
                    $existingBonus30k = $qualification->user->bonuses()
                        ->whereYear('created_at', date('Y', strtotime($month)))
                        ->whereMonth('created_at', date('m', strtotime($month)))
                        ->where('type', 'Bonus Power Plus')
                        ->where('description', 'like', '%30.000 point%')
                        ->exists();
                    
                    if (!$existingBonus30k) {
                        // Update bonus_amount: jika sudah ada bonus 15k, tambahkan dengan bonus 30k
                        $currentBonus = $qualification->bonus_amount;
                        $qualification->update(['bonus_amount' => $currentBonus + $bonus30k]);
                        
                        // Buat bonus baru untuk Grade 2
                        $qualification->user->bonuses()->create([
                            'type' => 'Bonus Power Plus',
                            'amount' => $bonus30k,
                            'description' => 'Bonus Power Plus untuk omzet kaki kecil 30.000 point bulan ' . $month . '.',
                            'created_at' => $month . '-01 00:00:00',
                            'updated_at' => $month . '-01 00:00:00',
                        ]);
                        Helper::rank($qualification->user, $bonus30k);
                    }
                });
        }
    }

    /**
     * Hitung omzet leg (kiri atau kanan) untuk tanggal tertentu (harian)
     */
    public static function calculateLegOmzet($user, $side, $date)
    {
        // Menggunakan upline_id untuk tree bonus (kecuali bonus sponsor dan generasi)
        $sponsors = $user->uplines()->where('placement_side', $side)
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
        
        // Menggunakan upline_id untuk tree bonus (kecuali bonus sponsor dan generasi)
        $sponsors = $user->uplines()->where('placement_side', $side)
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
     * Hitung omset per leg untuk bulan tertentu (bulanan - akumulasi)
     * Mengembalikan array dengan format: ["Leg 1" => 5000, "Leg 2" => 15500, "Leg 3" => 7000, ...]
     * Semua leg dihitung untuk Power Plus (tidak ada Leg Kiri yang dikecualikan)
     * Catatan: Untuk omset grup leg, HANYA menghitung poin dari root leg (anggota leg itu sendiri) saja
     * TIDAK ditambahkan dengan downline langsungnya atau downline lainnya
     * Contoh: Omset grup Leg 1 (cobalagi2) = hanya poin cobalagi2 sendiri
     */
    public static function calculateAllLegOmzetMonthly($user, $month)
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        $startDate = $date->format('Y-m-01');
        $endDate = $date->format('Y-m-t');
        
        // Ambil semua uplines berdasarkan urutan created_at
        // Upline pertama = Leg 1, Upline kedua = Leg 2, dst
        $allUplines = $user->uplines()
            ->whereHas('premiumUserPin')
            ->orderBy('created_at', 'asc')
            ->get();
        
        $legOmzets = [];
        
        foreach ($allUplines as $index => $upline) {
            // Leg 1 = index 0, Leg 2 = index 1, Leg 3 = index 2, dst
            $legName = 'Leg ' . ($index + 1);
            
            // Hitung omset bulanan HANYA dari root leg ini saja (tidak ditambahkan dengan downline)
            // Untuk omset grup, hanya gunakan poin dari root leg sendiri
            $omzet = 0;
            $monthlyPoins = $upline->dailyPoins()
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
            
            foreach ($monthlyPoins as $dailyPoin) {
                $omzet += $dailyPoin->pp + $dailyPoin->pr;
            }
            
            $legOmzets[$legName] = $omzet;
        }
        
        return $legOmzets;
    }

    /**
     * Hitung omset secara recursive untuk satu leg
     * Leg dihitung berdasarkan tree upline (semua downline yang berada di bawah leg ini)
     * 
     * @param User $user User yang akan dihitung omsetnya
     * @param string $startDate Tanggal mulai (format: Y-m-d)
     * @param string $endDate Tanggal akhir (format: Y-m-d)
     * @param bool $includeRoot Apakah root (user itu sendiri) harus dihitung. Default true untuk backward compatibility
     * @return int Total omset
     */
    private static function calculateLegOmzetRecursive($user, $startDate, $endDate, $includeRoot = true)
    {
        $omzet = 0;
        
        // Hitung omzet bulanan dari user ini (akumulasi semua hari dalam bulan)
        // Hanya hitung jika includeRoot = true
        if ($includeRoot) {
            $monthlyPoins = $user->dailyPoins()
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
            
            foreach ($monthlyPoins as $dailyPoin) {
                $omzet += $dailyPoin->pp + $dailyPoin->pr;
            }
        }
        
        // Recursive untuk semua downline-nya (semua yang memiliki upline_id = user ini)
        // Untuk downline, selalu hitung omset mereka (includeRoot = true)
        $downlines = $user->uplines()
            ->whereHas('premiumUserPin')
            ->get();
        
        foreach ($downlines as $downline) {
            $omzet += Helper::calculateLegOmzetRecursive($downline, $startDate, $endDate, true);
        }
        
        return $omzet;
    }

    /**
     * Dapatkan detail breakdown omset leg (siapa saja yang berkontribusi)
     * 
     * @param User $user User root leg
     * @param string $month Bulan dalam format Y-m
     * @return array Array berisi detail perhitungan: [['username' => '...', 'poin' => ...], ...]
     */
    public static function getLegOmzetBreakdown($user, $month)
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        $startDate = $date->format('Y-m-01');
        $endDate = $date->format('Y-m-t');
        
        $breakdown = [];
        Helper::getLegOmzetBreakdownRecursive($user, $startDate, $endDate, true, $breakdown);
        
        return $breakdown;
    }

    /**
     * Helper recursive untuk mendapatkan breakdown omset leg
     */
    private static function getLegOmzetBreakdownRecursive($user, $startDate, $endDate, $includeRoot, &$breakdown)
    {
        $userPoin = 0;
        
        // Hitung omzet bulanan dari user ini
        if ($includeRoot) {
            $monthlyPoins = $user->dailyPoins()
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
            
            foreach ($monthlyPoins as $dailyPoin) {
                $userPoin += $dailyPoin->pp + $dailyPoin->pr;
            }
            
            if ($userPoin > 0) {
                $breakdown[] = [
                    'username' => $user->username,
                    'name' => $user->name,
                    'poin' => $userPoin
                ];
            }
        }
        
        // Recursive untuk semua downline-nya
        $downlines = $user->uplines()
            ->whereHas('premiumUserPin')
            ->get();
        
        foreach ($downlines as $downline) {
            Helper::getLegOmzetBreakdownRecursive($downline, $startDate, $endDate, true, $breakdown);
        }
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
        
        // Hitung 4% dari omzet perusahaan (harian)
        $totalOmzet = Helper::transactionPoinDaily($date) * 1000;
        $totalUmrohAmount = round($totalOmzet * 0.04);
        
        // Dapatkan semua user yang memiliki minimal 3 sponsor langsung (premium dan aktif)
        $qualifiedUsers = User::whereHas('premiumUserPin', function ($q) {
            $q->whereHas('pin', function ($qPin) {
                $qPin->whereIn('name', ['Gold', 'Platinum']);
            });
        })
        ->where('is_active', true)
        ->get()
        ->filter(function ($user) {
            // Hitung sponsor langsung (disponsori langsung) yang premium dan aktif
            $sponsorCount = $user->sponsors()
                ->whereHas('premiumUserPin', function ($q) {
                    $q->whereHas('pin', function ($qPin) {
                        $qPin->whereIn('name', ['Gold', 'Platinum']);
                    });
                })
                ->where('is_active', true)
                ->count();
            return $sponsorCount >= 3;
        });
        
        // Hitung jumlah per member: 4% x Omset Nasional : Member Yg qualified
        $qualifiedCount = $qualifiedUsers->count();
        if ($qualifiedCount > 0) {
            $umrohAmountPerMember = round($totalUmrohAmount / $qualifiedCount);
        } else {
            $umrohAmountPerMember = 0;
        }
        
        foreach ($qualifiedUsers as $user) {
            // Simpan data harian
            UmrohTripDaily::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $date,
                ],
                [
                    'amount' => $umrohAmountPerMember,
                ]
            );
            
            $umrohSaving = UmrohTripSaving::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'year' => $year,
                ],
                [
                    'yearly_accumulation' => 0,
                    'claimed_amount' => 0,
                    'active_teams_count' => $user->sponsors()
                        ->whereHas('premiumUserPin', function ($q) {
                            $q->whereHas('pin', function ($qPin) {
                                $qPin->whereIn('name', ['Gold', 'Platinum']);
                            });
                        })
                        ->where('is_active', true)
                        ->count(),
                ]
            );
            
            // Tambahkan akumulasi (maksimal 50.000.000 per tahun)
            $newAccumulation = min($umrohSaving->yearly_accumulation + $umrohAmountPerMember, 50000000);
            $umrohSaving->update([
                'yearly_accumulation' => $newAccumulation,
            ]);
        }
    }

    /**
     * Hitung Global Profit Sharing (GPS) 5%
     * DIHITUNG HARIAN untuk Platinum aktivasi perdana (first pin Platinum, bukan upgrade/RO/maintain)
     * Dibagikan untuk semua Platinum perdana aktif setiap hari
     * Dana dikumpulkan di wallet cashback, maksimal Rp 22.500.000 (tidak ada batas tahunan)
     * Dipanggil setiap hari
     */
    public static function calculateGlobalProfitSharing($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        // Hitung total omzet perusahaan hari ini menggunakan transactionPoinDaily
        // Sama seperti perhitungan trip (4%), tapi untuk GPS 5%
        $totalOmzet = Helper::transactionPoinDaily($date) * 1000; // Convert poin ke rupiah (1 poin = 1000)
        $gpsPercent = 0.05; // 5% dari omzet
        $totalGpsAmount = round($totalOmzet * $gpsPercent);
        
        // Dapatkan semua user Platinum yang aktivasi perdana (first pin Platinum, bukan upgrade/RO/maintain)
        // Hanya Platinum yang JOIN dari awal (type = 'premium', bukan 'upgrade')
        // Dan harus memiliki profit_sharings record dengan is_perdana_platinum = true
        $platinumUsers = User::whereHas('profitSharings', function ($q) {
            $q->where('is_perdana_platinum', true);
        })
        ->whereHas('premiumUserPin', function ($q) {
            $q->whereHas('pin', function ($qPin) {
                // Hanya Platinum yang JOIN dari awal (type = 'premium', bukan 'upgrade')
                $qPin->where('name', 'Platinum')->where('type', 'premium');
            });
        })
        ->where('is_active', true)
        ->get();
        
        // Hitung jumlah per member: GPS Amount : Jumlah Platinum Perdana Aktif
        $platinumCount = $platinumUsers->count();
        if ($platinumCount > 0) {
            $gpsAmountPerMember = round($totalGpsAmount / $platinumCount);
        } else {
            $gpsAmountPerMember = 0;
        }
        
        foreach ($platinumUsers as $user) {
            // Simpan data harian
            GlobalProfitSharingDaily::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $date,
                ],
                [
                    'amount' => $gpsAmountPerMember,
                ]
            );
            
            // Ambil atau buat saving record
            $gpsSaving = GlobalProfitSharingSaving::firstOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'daily_accumulation' => 0,
                    'wallet_cashback' => 0,
                    'date' => $date,
                ]
            );
            
            // Tambahkan akumulasi harian
            $dailyAccumulation = $gpsSaving->daily_accumulation + $gpsAmountPerMember;
            // Batas wallet cashback maksimal 22.500.000 (tidak ada batas tahunan)
            $walletCashback = min($dailyAccumulation, 22500000);
            
            $gpsSaving->update([
                'daily_accumulation' => $dailyAccumulation,
                'wallet_cashback' => $walletCashback,
                'date' => $date,
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
        
        // Total belanja dari automaintain RO (base pin dengan is_ro = true)
        $automaintainRO = $user->userPins()
            ->whereHas('pin', function($q) {
                $q->whereIn('name', ['Gold', 'Gold Upgrade Platinum', 'Platinum']);
            })
            ->where('is_ro', true)
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
     * Cek dan trigger Auto RO berdasarkan 170 PV dalam masa aktif
     * Mirip dengan Automaintain, tapi trigger dari belanja online yang mencapai 170 PV
     * Dipanggil setelah transaksi dibuat (Transaction atau OfficialTransaction)
     * 
     * @param User $user User yang akan dicek
     * @param int $transactionPoin Poin dari Transaction (general) yang baru dibuat
     * @param int $officialTransactionPoin Poin dari OfficialTransaction yang baru dibuat
     */
    public static function checkAndTriggerAutoROFromPV($user, $transactionPoin = 0, $officialTransactionPoin = 0)
    {
        // Cek apakah user masih dalam masa aktif
        if (!$user->active_until) {
            return false;
        }

        // Cek apakah user punya pin Gold, Gold Upgrade Platinum, atau Platinum
        $userPin = $user->premiumUserPin;
        if (!$userPin || !$userPin->pin) {
            return false;
        }

        $pin = $userPin->pin;
        
        // Hanya untuk base pin: Gold, Gold Upgrade Platinum, atau Platinum
        if (!in_array($pin->name, ['Gold', 'Gold Upgrade Platinum', 'Platinum'])) {
            return false;
        }

        // Hitung total PV yang terkumpul dalam masa aktif
        // Periode aktif: dari activeFrom sampai activeUntil
        // Tapi untuk perhitungan PV, kita hitung semua transaksi yang dibuat sebelum active_until
        // (bukan hanya yang dalam periode aktif, karena transaksi bisa dibuat sebelum periode aktif dimulai)
        $activeFrom = Carbon::parse($user->active_until)->subDays($user->active_days_initial ?? 45);
        $activeUntil = Carbon::parse($user->active_until);
        
        // Hitung PV dari transaksi umum
        // Hitung semua transaksi yang dibuat sebelum active_until (bukan hanya dalam periode aktif)
        // Catatan: Query ini menghitung transaksi dengan status 'paid', 'packed', 'shipped', 'received'
        // Saat dipanggil dari confirm(), transaksi baru saja di-update menjadi 'paid', jadi sudah terhitung
        $transactionPoinTotal = Transaction::where('user_id', $user->id)
            ->where('type', 'general')
            ->where('poin', '>', 0)
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->where('created_at', '<=', $activeUntil)
            ->sum('poin');
        
        // JANGAN tambahkan $transactionPoin lagi karena:
        // - Saat dipanggil dari confirm(), transaksi sudah di-update status menjadi 'paid' dan sudah terhitung di query di atas
        // - Parameter $transactionPoin hanya untuk backward compatibility, tidak digunakan lagi
        
        // Hitung PV dari official transaction
        // Hitung semua transaksi yang dibuat sebelum active_until (bukan hanya dalam periode aktif)
        // Catatan: Query ini menghitung transaksi dengan status 'paid', 'packed', 'shipped', 'received'
        // Saat dipanggil dari confirm(), transaksi baru saja di-update menjadi 'paid', jadi sudah terhitung
        $officialPoin = OfficialTransaction::where('user_id', $user->id)
            ->where('poin', '>', 0)
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->where('created_at', '<=', $activeUntil)
            ->sum('poin');
        
        // JANGAN tambahkan $officialTransactionPoin lagi karena:
        // - Saat dipanggil dari confirm(), transaksi sudah di-update status menjadi 'paid' dan sudah terhitung di query di atas
        // - Parameter $officialTransactionPoin hanya untuk backward compatibility, tidak digunakan lagi
        
        // Hitung PV dari daily poin dalam masa aktif
        $dailyPoinPV = $user->dailyPoins()
            ->where('pv', '>', 0)
            ->whereBetween('date', [$activeFrom->format('Y-m-d'), $activeUntil->format('Y-m-d')])
            ->sum('pv');
        
        $totalPVInActive = $transactionPoinTotal + $officialPoin + $dailyPoinPV;
        
        // Cek apakah sudah mencapai 170 PV dalam masa aktif
        if ($totalPVInActive >= 170) {
            // Hitung berapa kali Auto RO seharusnya sudah dibuat berdasarkan kelipatan 170 PV
            // Setiap kelipatan 170 PV = 1 Auto RO (170, 340, 510, 680, dst)
            $expectedAutoROCount = floor($totalPVInActive / 170);
            
            // Hitung berapa Auto RO yang sudah ada
            // Hitung semua Auto RO yang dibuat sebelum active_until (bukan hanya dalam periode aktif)
            $existingAutoROCount = $user->userPins()
                ->whereHas('pin', function($q) use ($pin) {
                    $q->where('name', $pin->name);
                })
                ->where('is_ro', true)
                ->where('is_used', true)
                ->where('created_at', '<=', $activeUntil)
                ->count();
            
            // Jika masih ada Auto RO yang belum dibuat, buat semua yang terlewat sekaligus
            // Setiap Auto RO dibuat dengan tanggal sesuai milestone PV (170, 340, 510, dst)
            if ($expectedAutoROCount > $existingAutoROCount) {
                // Gunakan database lock untuk mencegah race condition dan duplikasi
                // Lock user record untuk memastikan hanya 1 proses yang membuat Auto RO pada saat yang sama
                DB::beginTransaction();
                try {
                    // Lock user record untuk mencegah concurrent access
                    // Gunakan fresh() untuk memastikan data terbaru
                    $userLocked = User::lockForUpdate()->find($user->id);
                    if (!$userLocked) {
                        DB::rollBack();
                        return false;
                    }
                    
                    // Refresh pin juga untuk memastikan data terbaru
                    $userLocked->load('premiumUserPin.pin');
                    $pin = $userLocked->premiumUserPin->pin;
                    
                    // Re-check existingAutoROCount setelah lock (untuk mencegah duplikasi)
                    // Pastikan kita menghitung ulang dengan data terbaru
                    $existingAutoROCountAfterLock = $userLocked->userPins()
                        ->whereHas('pin', function($q) use ($pin) {
                            $q->where('name', $pin->name);
                        })
                        ->where('is_ro', true)
                        ->where('is_used', true)
                        ->where('created_at', '<=', $activeUntil)
                        ->count();
                    
                    // Jika masih ada Auto RO yang belum dibuat setelah lock
                    if ($expectedAutoROCount > $existingAutoROCountAfterLock) {
                        $missingAutoROCount = $expectedAutoROCount - $existingAutoROCountAfterLock;
                        
                        // Kumpulkan semua transaksi untuk menentukan tanggal Auto RO berdasarkan milestone PV
                        $allTransactions = collect();
                        
                        // Ambil transaksi umum
                        $transactions = Transaction::where('user_id', $userLocked->id)
                            ->where('type', 'general')
                            ->where('poin', '>', 0)
                            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
                            ->where('created_at', '<=', $activeUntil)
                            ->orderBy('created_at', 'asc')
                            ->get();
                        
                        foreach ($transactions as $t) {
                            $allTransactions->push([
                                'date' => Carbon::parse($t->created_at),
                                'poin' => $t->poin,
                            ]);
                        }
                        
                        // Ambil official transaction
                        $officialTransactions = OfficialTransaction::where('user_id', $userLocked->id)
                            ->where('poin', '>', 0)
                            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
                            ->where('created_at', '<=', $activeUntil)
                            ->orderBy('created_at', 'asc')
                            ->get();
                        
                        foreach ($officialTransactions as $ot) {
                            $allTransactions->push([
                                'date' => Carbon::parse($ot->created_at),
                                'poin' => $ot->poin,
                            ]);
                        }
                        
                        // Ambil daily poin
                        $dailyPoins = $userLocked->dailyPoins()
                            ->where('pv', '>', 0)
                            ->whereBetween('date', [$activeFrom->format('Y-m-d'), $activeUntil->format('Y-m-d')])
                            ->orderBy('date', 'asc')
                            ->get();
                        
                        foreach ($dailyPoins as $dp) {
                            $allTransactions->push([
                                'date' => Carbon::parse($dp->date),
                                'poin' => $dp->pv,
                            ]);
                        }
                        
                        // Urutkan semua berdasarkan tanggal
                        $allTransactions = $allTransactions->sortBy('date');
                        
                        // Tentukan tanggal untuk setiap Auto RO berdasarkan milestone PV
                        $accumulatedPV = 0;
                        $roDates = [];
                        
                        foreach ($allTransactions as $item) {
                            $accumulatedPV += $item['poin'];
                            
                            // Cek setiap kelipatan 170 PV
                            $currentExpectedRO = floor($accumulatedPV / 170);
                            
                            // Jika ada Auto RO baru yang seharusnya dibuat
                            while ($currentExpectedRO > count($roDates)) {
                                $roDates[] = $item['date'];
                            }
                        }
                        
                        // Jika masih kurang (misalnya karena ada gap), gunakan tanggal terakhir transaksi atau waktu sekarang
                        while (count($roDates) < $expectedAutoROCount) {
                            $lastDate = $allTransactions->last() ? $allTransactions->last()['date'] : Carbon::now();
                            $roDates[] = $lastDate;
                        }
                        
                        // Ambil hanya tanggal untuk Auto RO yang terlewat (mulai dari yang sudah ada)
                        $roDatesToCreate = array_slice($roDates, $existingAutoROCountAfterLock, $missingAutoROCount);
                        
                        // Buat semua Auto RO yang terlewat
                        $createdCount = 0;
                        foreach ($roDatesToCreate as $roDate) {
                            $roUserPin = $userLocked->userPins()->create([
                                'buyer_id' => $userLocked->id,
                                'pin_id' => $pin->id,
                                'code' => strtoupper(\Illuminate\Support\Str::random(6)),
                                'name' => $pin->name,
                                'price' => $pin->ro_price ?? ($pin->name == 'Platinum' ? 12750000 : 1700000), // Gunakan harga RO
                                'level' => $pin->level,
                                'is_used' => true,
                                'is_ro' => true, // Tandai sebagai Repeat Order
                                'created_at' => $roDate,
                                'updated_at' => $roDate,
                            ]);
                            
                            Helper::pinHistory($roUserPin);
                            Helper::upgrade($roUserPin); // Ini akan membuat bonus generasi ke atas
                            
                            $createdCount++;
                        }
                        
                        // Perpanjang masa aktif 45 hari untuk setiap Auto RO yang dibuat
                        // Setiap Auto RO memperpanjang masa aktif 45 hari
                        if ($createdCount > 0 && $userLocked->active_until) {
                            $newActiveUntil = Carbon::parse($userLocked->active_until)->addDays(45 * $createdCount);
                            $userLocked->update([
                                'active_until' => $newActiveUntil,
                                'is_active' => true,
                            ]);
                        }
                        
                        DB::commit();
                        
                        \Log::info("Auto RO dibuat untuk {$userLocked->username}: {$createdCount} Auto RO (PV: {$totalPVInActive}, Expected: {$expectedAutoROCount}, Existing: {$existingAutoROCountAfterLock})");
                        
                        return true;
                    } else {
                        // Auto RO sudah dibuat oleh proses lain, skip
                        DB::rollBack();
                        return false;
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error creating Auto RO: ' . $e->getMessage());
                    return false;
                }
            }
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