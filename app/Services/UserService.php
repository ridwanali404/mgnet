<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pin;
use App\Models\Transaction;
use App\Models\OfficialTransaction;
use DateTime;
use Carbon\Carbon;
use DB;

class UserService
{
    /**
     * Get monthly rank for user
     */
    public function monthlyRank(User $user, $month)
    {
        if ($user->premiumUserPin) {
            if ($user->monthlyAgenSponsors($month)->count() >= 10) {
                return 'Distributor';
            }
            if ($user->monthlyPremiumSponsors($month)->count() >= 10) {
                return 'Agen';
            }
        }
        return false;
    }

    /**
     * Get monthly poin for user
     */
    public function monthlyPoin(User $user, $month)
    {
        if (\App\Models\KeyValue::where('key', 'poin')->value('value') == 'enable') {
            $userPoin = $user->userPoins()->whereYear('date', date('Y', strtotime($month)))->whereMonth('date', date('m', strtotime($month)))->first();
            if ($userPoin) {
                return $userPoin->poin;
            }
        }
        // get ro poin from transaction
        $t = $user->paidTransaction($month)->sum('poin');
        // get ro poin from official transaction
        $ot = $user->monthlyOfficial($month)->sum('poin');
        // get ro poin from daily poin
        $dp = $user->monthlyDailyPoins($month)->sum('pv');
        return $t + $ot + $dp;
    }

    /**
     * Check if pin is usable for user
     */
    public function checkUsablePin(User $user, Pin $pin)
    {
        if (!$user->userPin && $pin->type == 'premium') {
            return true;
        }
        if (in_array($user->userPin->pin->name, ['Free Member', 'CR Reseller']) && $pin->type == 'premium') {
            return true;
        }
        if (in_array($user->userPin->pin->name, ['Basic'])) {
            if (in_array($pin->name, ['Basic Upgrade Silver', 'Basic Upgrade Gold', 'Basic Upgrade Platinum', 'Generasi', 'Generasi Up'])) {
                return true;
            }
        }
        if (in_array($user->userPin->pin->name, ['Silver', 'Basic Upgrade Silver', 'Generasi', 'Generasi Up'])) {
            if (in_array($pin->name, ['Silver Upgrade Gold', 'Silver Upgrade Platinum'])) {
                return true;
            }
        }
        if (in_array($user->userPin->pin->name, ['Gold', 'Basic Upgrade Gold', 'Silver Upgrade Gold'])) {
            if (in_array($pin->name, ['Gold Upgrade Platinum'])) {
                return true;
            }
        }
        if (!$user->userPins()->whereIn('name', ['Generasi', 'Generasi Up'])->count()) {
            if (in_array($pin->name, ['Generasi', 'Generasi Up'])) {
                return true;
            }
        }
        if (!$user->userPins()->where('name', 'like', '%BSM%')->count()) {
            if (str_contains($pin->name, 'BSM')) {
                return true;
            }
        }
        $bsm34 = ['BSM GOLD', 'BSM PLATINUM', 'BSM GOLD UP', 'BSM PLATINUM UP', 'BSM GOLD Automaintain'];
        if (!$user->userPins()->whereIn('name', $bsm34)->count()) {
            if (str_contains($pin->name, 'BSM')) {
                return true;
            }
        }
        if ($user->userPins()->whereIn('name', $bsm34)->count()) {
            if (str_contains($pin->name, 'PIN PAKET RO')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get color for user based on pin
     */
    public function color(User $user)
    {
        $color = 'white';
        switch ($user->userPin?->pin->name_short) {
            case 'Basic':
                $color = 'dark';
                break;
            case 'Silver':
                $color = 'muted';
                break;
            case 'Gold':
                $color = 'warning';
                break;
            case 'Platinum':
                $color = 'danger';
                break;
            default:
                break;
        }
        return $color;
    }

    /**
     * Check if user is monthly qualified
     */
    public function monthlyQualified(User $user, $month)
    {
        $qty = $this->monthlyPoin($user, $month);
        if ($qty >= 39) {
            return true;
        }
        return false;
    }

    /**
     * Check if user is monthly royalty qualified
     */
    public function monthlyRoyaltyQualified(User $user, $month)
    {
        $qty = $this->monthlyPoin($user, $month);
        if ($qty >= 250) {
            return true;
        }
        return false;
    }

    /**
     * Get monthly potency for user
     */
    public function monthlyPotency(User $user, $month)
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        $transactions = Transaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))
            ->where('type', 'general')
            ->where('poin', '>', 0)
            ->whereHas('user', function ($q) use ($user) {
                $q->where('created_at', '>', $user->created_at);
            })
            ->whereHas('carts', function ($q_cart) {
                $q_cart->whereHas('product', function ($q_product) {
                    $q_product->where('is_ro', true);
                });
            })
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->latest();
        $userIdArray = clone $transactions;
        $users = $userIdArray->groupBy('user_id')->pluck('user_id');
        $potency = collect();
        foreach ($users as $userId) {
            $transactionUser = User::find($userId);
            $sponsor = $transactionUser->sponsor;
            $i = 1;
            while ($i <= 10 && $sponsor) {
                if ($sponsor->id == $user->id) {
                    $percent = \App\Models\KeyValue::where('key', 'monthly_ro_unilevel_' . $i)->value('value');
                    $userTransactions = clone $transactions;
                    $userTransactions = $userTransactions->where('user_id', $userId)->get();
                    foreach ($userTransactions as $ut) {
                        $carts = '';
                        foreach ($ut->carts as $key => $cart) {
                            if ($key + 1 == $ut->carts()->count()) {
                                if ($key == 0) {
                                    $carts .= $cart->qty . ' ' . ($cart->product ? $cart->product->name : $cart->name ?? 'Produk telah dihapus') . ' (' . $cart->poin_total . ' poin)';
                                } else {
                                    $carts .= 'dan ' . $cart->qty . ' ' . ($cart->product ? $cart->product->name : $cart->name ?? 'Produk telah dihapus') . ' (' . $cart->poin_total . ' poin)';
                                }
                            } else {
                                $carts .= $cart->qty . ' ' . ($cart->product ? $cart->product->name : $cart->name ?? 'Produk telah dihapus') . ' (' . $cart->poin_total . ' poin)' . ', ';
                            }
                        }
                        $potency->push([
                            'type' => 'Bonus Unilevel RO',
                            'amount' => round($ut->poin * 1000 * $percent / 100),
                            'description' => 'Bonus Unilevel RO dari belanja ' . $transactionUser->username . '. Belanja ' . $carts . '. Generasi ke-' . $i . ' sebesar ' . $percent . '% dari ' . $ut->poin . ' poin.',
                            'created_at' => $ut->created_at,
                        ]);
                    }
                    break;
                }
                if (!$sponsor->member) {
                    break;
                }
                if ($sponsor->member->member_phase_name != 'User Free' && $this->monthlyQualified($sponsor, $month)) {
                    $i++;
                }
                $sponsor = $sponsor->sponsor;
            }
        }
        // transaction non member
        $transactions = Transaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))
            ->where('type', 'general')
            ->where('poin', '>', 0)
            ->whereNull('user_id')
            ->whereNotNull('sponsor_id')
            ->whereHas('carts', function ($q_cart) {
                $q_cart->whereHas('product', function ($q_product) {
                    $q_product->where('is_ro', true);
                });
            })
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->latest();
        $userIdArray = clone $transactions;
        $users = $userIdArray->groupBy('sponsor_id')->pluck('sponsor_id');
        foreach ($users as $userId) {
            $transactionUser = User::find($userId);
            $sponsor = $transactionUser->sponsor;
            $i = 1;
            while ($i <= 10 && $sponsor) {
                if ($sponsor->id == $user->id) {
                    $percent = \App\Models\KeyValue::where('key', 'monthly_ro_unilevel_' . $i)->value('value');
                    $userTransactions = clone $transactions;
                    $userTransactions = $userTransactions->where('sponsor_id', $userId)->get();
                    foreach ($userTransactions as $ut) {
                        $carts = '';
                        foreach ($ut->carts as $key => $cart) {
                            if ($key + 1 == $ut->carts()->count()) {
                                if ($key == 0) {
                                    $carts .= $cart->qty . ' ' . ($cart->product ? $cart->product->name : $cart->name ?? 'Produk telah dihapus') . ' (' . $cart->poin_total . ' poin)';
                                } else {
                                    $carts .= 'dan ' . $cart->qty . ' ' . ($cart->product ? $cart->product->name : $cart->name ?? 'Produk telah dihapus') . ' (' . $cart->poin_total . ' poin)';
                                }
                            } else {
                                $carts .= $cart->qty . ' ' . ($cart->product ? $cart->product->name : $cart->name ?? 'Produk telah dihapus') . ' (' . $cart->poin_total . ' poin)' . ', ';
                            }
                        }
                        $potency->push([
                            'type' => 'Bonus Unilevel RO',
                            'amount' => round($ut->poin * 1000 * $percent / 100),
                            'description' => 'Bonus Unilevel RO dari belanja ' . $transactionUser->username . '. Belanja ' . $carts . '. Generasi ke-' . $i . ' sebesar ' . $percent . '% dari ' . $ut->poin . ' poin.',
                            'created_at' => $ut->created_at,
                        ]);
                    }
                    break;
                }
                if (!$sponsor->member) {
                    break;
                }
                if ($sponsor->member->member_phase_name != 'User Free' && $this->monthlyQualified($sponsor, $month)) {
                    $i++;
                }
                $sponsor = $sponsor->sponsor;
            }
        }
        $ot = OfficialTransaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->whereIn('status', ['paid', 'packed', 'shipped', 'received'])->latest();
        $userIdArray = clone $ot;
        $users = $userIdArray->groupBy('user_id')->pluck('user_id');
        foreach ($users as $userId) {
            $transactionUser = User::find($userId);
            $sponsor = $transactionUser->sponsor;
            $i = 1;
            while ($i <= 10 && $sponsor) {
                if ($sponsor->id == $user->id) {
                    $percent = \App\Models\KeyValue::where('key', 'monthly_ro_unilevel_' . $i)->value('value');
                    $userTransactions = clone $ot;
                    $userTransactions = $userTransactions->where('user_id', $userId)->get();
                    foreach ($userTransactions as $ut) {
                        $potency->push([
                            'type' => 'Bonus Unilevel RO',
                            'amount' => round($ut->poin * 1000 * $percent / 100),
                            'description' => 'Bonus Unilevel RO dari belanja official ' . $transactionUser->username . '. Belanja ' . $ut->qty . ' ' . ($ut->product->name ?? 'Produk telah dihapus') . ' (' . $ut->poin . ' poin)' . '. Generasi ke-' . $i . ' sebesar ' . $percent . '% dari ' . $ut->poin . ' poin.',
                            'created_at' => $ut->created_at,
                        ]);
                    }
                    break;
                }
                if (!$sponsor->member) {
                    break;
                }
                if ($sponsor->member->member_phase_name != 'User Free' && $this->monthlyQualified($sponsor, $month)) {
                    $i++;
                }
                $sponsor = $sponsor->sponsor;
            }
        }
        $users = User::whereHas('userPin', function ($q) {
            $q->whereHas('pin', function ($q_pin) {
                $q_pin->whereIn('name', ['Gold', 'Basic Upgrade Gold', 'Silver Upgrade Gold', 'Platinum', 'Basic Upgrade Platinum', 'Silver Upgrade Platinum', 'Gold Upgrade Platinum']);
            });
        })->whereHas('dailyPoins', function ($q) use ($date) {
            $q->where('pv', '>', 0);
        })->get();
        foreach ($users as $transactionUser) {
            $sponsor = $transactionUser->sponsor;
            $i = 1;
            while ($i <= 10 && $sponsor) {
                if ($sponsor->id == $user->id) {
                    $percent = \App\Models\KeyValue::where('key', 'monthly_ro_unilevel_' . $i)->value('value');
                    $dp = $transactionUser->dailyPoins()->where('pv', '>', 0)->whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->latest()->get();
                    foreach ($dp as $a) {
                        $potency->push([
                            'type' => 'Bonus Unilevel RO',
                            'amount' => round($a->pv * 1000 * $percent / 100),
                            'description' => 'Bonus Unilevel RO dari paket pin ' . $a->user->username . ' sejumlah ' . $a->pv . ' poin' . '. Generasi ke-' . $i . ' sebesar ' . $percent . '% dari ' . $a->pv . ' poin.',
                            'created_at' => $a->date,
                        ]);
                    }
                    break;
                }
                if (!$sponsor->member) {
                    break;
                }
                if ($sponsor->member->member_phase_name != 'User Free' && $this->monthlyQualified($sponsor, $month)) {
                    $i++;
                }
                $sponsor = $sponsor->sponsor;
            }
        }
        return $potency;
    }

    /**
     * Get level of user relative to another user
     */
    public function level(User $user, User $targetUser)
    {
        $level = 1;
        $this->digLevel($user, $targetUser, $level);
        return $level;
    }

    /**
     * Helper method to dig level
     */
    private function digLevel(User $user, User $targetUser, &$level)
    {
        if (!$targetUser->sponsor_id) {
            return 1;
        }
        if ($targetUser->id == $user->id) {
            return $level;
        }
        $level++;
        if ($targetUser->sponsor) {
            $this->digLevel($user, $targetUser->sponsor, $level);
        }
    }

    /**
     * Get all referrals recursively
     */
    public function referrals(User $user)
    {
        $downlines = User::whereNull('id')->get();
        $this->recursive($user, $downlines);
        return $downlines;
    }

    /**
     * Get free referrals recursively
     */
    public function freeReferrals(User $user)
    {
        $downlines = User::whereNull('id')->get();
        $this->freeRecursive($user, $downlines);
        return $downlines;
    }

    /**
     * Get premium referrals recursively
     */
    public function premiumReferrals(User $user)
    {
        $downlines = User::whereNull('id')->get();
        $this->premiumRecursive($user, $downlines);
        return $downlines;
    }

    /**
     * Get agen referrals recursively
     */
    public function agenReferrals(User $user)
    {
        $downlines = User::whereNull('id')->get();
        $this->agenRecursive($user, $downlines);
        return $downlines;
    }

    /**
     * Get distributor referrals recursively
     */
    public function distributorReferrals(User $user)
    {
        $downlines = User::whereNull('id')->get();
        $this->distributorRecursive($user, $downlines);
        return $downlines;
    }

    /**
     * Recursive method for distributor referrals
     */
    private function distributorRecursive(User $user, $downlines)
    {
        if ($user->sponsors) {
            foreach ($user->sponsors as $a) {
                if ($a->rank == 'Distributor') {
                    $downlines->push($a);
                }
                $this->distributorRecursive($a, $downlines);
            }
        }
        return;
    }

    /**
     * Recursive method for agen referrals
     */
    private function agenRecursive(User $user, $downlines)
    {
        if ($user->sponsors) {
            foreach ($user->sponsors as $a) {
                if ($a->rank == 'Agen') {
                    $downlines->push($a);
                }
                $this->agenRecursive($a, $downlines);
            }
        }
        return;
    }

    /**
     * Recursive method for premium referrals
     */
    private function premiumRecursive(User $user, $downlines)
    {
        if ($user->sponsors) {
            foreach ($user->sponsors as $a) {
                if ($a->premiumUserPin()->count()) {
                    $downlines->push($a);
                }
                $this->premiumRecursive($a, $downlines);
            }
        }
        return;
    }

    /**
     * Recursive method for free referrals
     */
    private function freeRecursive(User $user, $downlines)
    {
        if ($user->sponsors) {
            foreach ($user->sponsors as $a) {
                if (!$a->premiumUserPin()->count()) {
                    $downlines->push($a);
                }
                $this->freeRecursive($a, $downlines);
            }
        }
        return;
    }

    /**
     * Recursive method for all referrals
     */
    private function recursive(User $user, $downlines)
    {
        if ($user->sponsors) {
            foreach ($user->sponsors as $a) {
                $downlines->push($a);
                $this->recursive($a, $downlines);
            }
        }
        return;
    }

    /**
     * Check if user is monoleg
     */
    public function isMonoleg(User $user)
    {
        return $user->userPins()->count() > 0;
    }

    /**
     * Check if user is week active
     */
    public function isWeekActive(User $user, $week)
    {
        if ($user->userPin?->pin->level > 2 && $user->activeWeeks()->where('week', $week)->count()) {
            return true;
        }
        return false;
    }

    /**
     * Check if user is already automaintain for month
     */
    public function isAlreadyAutomaintain(User $user, $month)
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        return $user->userPins()->whereIn('name', ['PIN PAKET RO', 'BSM GOLD Automaintain'])
            ->where('is_used', true)
            ->whereYear('created_at', $date->format('Y'))
            ->whereMonth('created_at', $date->format('m'))
            ->count();
    }
}

