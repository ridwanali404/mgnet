<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use DB;
use DateTime;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use \Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'active_until' => 'date',
        'roles' => 'array',
    ];

    protected $appends = [
        // 'rank',
        // 'image_path',
    ];

    public function getParentKeyName()
    {
        return 'sponsor_id';
    }

    public function getLocalKeyName()
    {
        return 'id';
    }

    public function getRankAttribute()
    {
        if ($this->premiumUserPin) {
            if ($this->agenSponsors()->count() >= 10) {
                return 'Distributor';
            }
            if ($this->premiumSponsors()->count() >= 10) {
                return 'Agen';
            }
        }
    }

    public function getImagePathAttribute()
    {
        return 'img/default_user_image.png';
        if ($this->image) {
            return $this->image;
        }

    }

    public function monthlyRank($month)
    {
        if ($this->premiumUserPin) {
            if ($this->monthlyPremiumSponsors($month)->count() >= 10) {
                return 'Agen';
            }
            if ($this->monthlyAgenSponsors($month)->count() >= 10) {
                return 'Distributor';
            }
        }
        return false;
    }

    public function address()
    {
        return $this->hasOne(Address::class)->where('is_active', true);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function userPin(): HasOne
    {
        return $this->hasOne(UserPin::class)->ofMany([
            'level' => 'max',
            'created_at' => 'max',
        ]);
    }

    public function premiumUserPin(): HasOne
    {
        return $this->hasOne(UserPin::class)->whereHas('pin', function ($q_pin) {
            $q_pin->whereIn('type', ['premium', 'upgrade'])->where('name', '!=', 'CR Reseller');
        })->latestOfMany();
    }

    public function userPins()
    {
        return $this->hasMany(UserPin::class);
    }

    public function boughtUserPins()
    {
        return $this->hasMany(UserPin::class, 'buyer_id');
    }

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function sponsors()
    {
        return $this->hasMany(User::class, 'sponsor_id');
    }

    public function freeSponsors()
    {
        return $this->sponsors()->whereHas('userPin', function ($q_userPin) {
            $q_userPin->whereDoesntHave('pin', function ($q_pin) {
                $q_pin->where('type', 'premium');
            });
        });
    }

    public function premiumSponsors()
    {
        return $this->sponsors()->whereHas('premiumUserPin');
    }

    public function agenSponsors()
    {
        return $this->premiumSponsors()->has('premiumSponsors', '>=', 10);
        if ($this->premiumUserPin()->count()) {
            return $this->premiumSponsors()->whereHas('premiumSponsors', function ($q_premiumSponsors) {
                $q_premiumSponsors->havingRaw('COUNT(*) >= 10');
            });
        }
        // return $this->sponsors()->whereNull('id');
        // ->has('premiumSponsors', '>=', 10);
        // ->whereHas('premiumSponsors', function($q_premiumSponsors) {
        //     $q_premiumSponsors->
        // });
    }

    public function monthlyPremiumSponsors($month)
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        return $this->sponsors()->whereHas('userPin', function ($q_userPin) use ($date) {
            $q_userPin->whereHas('pin', function ($q_pin) {
                $q_pin->where('type', 'premium');
            })->where('updated_at', '<=', $date->format('Y-m-d H:i:s'));
        });
    }

    public function monthlyAgenSponsors($month)
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        return $this->sponsors()->whereHas('userPin', function ($q_userPin) use ($date) {
            $q_userPin->whereHas('pin', function ($q_pin) {
                $q_pin->where('type', 'premium');
            })->where('updated_at', '<=', $date->format('Y-m-d H:i:s'));
        })
            ->has('premiumSponsors', '>=', 10);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function sponsorCarts()
    {
        return Cart::whereIn('transaction_id', $this->sponsorTransactions()->pluck('id'));
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function sponsorTransactions()
    {
        return $this->hasMany(Transaction::class, 'sponsor_id');
    }

    public function paidTransaction($month)
    {
        return $this->transactions()->where('type', 'general')->where('poin', '>', 0)->whereIn('status', ['paid', 'packed', 'shipped', 'received'])->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)));
    }

    public function bonuses()
    {
        return $this->hasMany(Bonus::class);
    }

    public function profitSharings()
    {
        return $this->hasMany(ProfitSharing::class);
    }

    public function powerPlusQualifications()
    {
        return $this->hasMany(PowerPlusQualification::class);
    }

    public function umrohTripSavings()
    {
        return $this->hasMany(UmrohTripSaving::class);
    }

    public function weeklyBonuses($week)
    {
        $date = Carbon::parse($week);
        return $this->bonuses()->whereIn('type', ['Komisi Pasangan', 'Bonus Generasi'])->whereBetween(DB::raw('DATE(`created_at`)'), [
            $date->startofweek()->format('Y-m-d'),
            $date->endofweek()->format('Y-m-d')
        ]);
    }

    public function unpaidWeeklyBonuses($week)
    {
        $bonuses = $this->bonuses()->whereDate('created_at', '<=', Carbon::parse($week)->endofweek()->format('Y-m-d'))->whereNull('paid_at');
        return $bonuses->whereIn('type', ['Komisi Pasangan', 'Bonus Generasi']);
        // if ($this->isWeekActive($week)) {
        //     return $bonuses->whereIn('type', ['Komisi Pasangan', 'Bonus Generasi']);
        // }
        // return $bonuses->whereIn('type', ['Komisi Sponsor']);
    }

    public function monthlyBonuses($month)
    {
        return $this->bonuses()->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->where(function ($q) {
            $q->where('type', 'Komisi Penjualan');
            $q->orWhere('type', 'Bonus Unilevel RO');
            $q->orWhere('type', 'Bonus Royalti Profit Sharing 13%');
            $q->orWhere('type', 'Bonus Royalti Profit Sharing 70%');
            $q->orWhere('type', 'Bonus Royalti Profit Sharing 30%');
        });
    }

    public function unpaidMonthlyBonuses($month)
    {
        return $this->bonuses()->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->where(function ($q) {
            $q->where('type', 'Komisi Penjualan');
            $q->orWhere('type', 'Bonus Unilevel RO');
            $q->orWhere('type', 'Bonus Royalti Profit Sharing 13%');
            $q->orWhere('type', 'Bonus Royalti Profit Sharing 70%');
            $q->orWhere('type', 'Bonus Royalti Profit Sharing 30%');
        })->whereNull('paid_at');
    }

    public function monthlyCashbackBonuses($month)
    {
        return $this->bonuses()->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->where(function ($q) {
            $q->where('type', 'Komisi Penjualan');
        });
    }

    public function monthlyUnilevelROBonuses($month)
    {
        return $this->bonuses()->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->where(function ($q) {
            $q->where('type', 'Bonus Unilevel RO');
        });
    }

    public function monthlyProfitSharing13Bonuses($month)
    {
        return $this->bonuses()->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->where(function ($q) {
            $q->where('type', 'Bonus Royalti Profit Sharing 13%');
        });
    }

    public function monthlyProfitSharing70Bonuses($month)
    {
        return $this->bonuses()->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->where(function ($q) {
            $q->where('type', 'Bonus Royalti Profit Sharing 70%');
        });
    }

    public function monthlyProfitSharing30Bonuses($month)
    {
        return $this->bonuses()->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->where(function ($q) {
            $q->where('type', 'Bonus Royalti Profit Sharing 30%');
        });
    }

    public function monthlyQualified($month)
    {
        $qty = $this->monthlyPoin($month);
        if ($qty >= 39) {
            return true;
        }
        return false;
    }

    public function monthlyRoyaltyQualified($month)
    {
        $qty = $this->monthlyPoin($month);
        if ($qty >= 250) {
            return true;
        }
        return false;
    }

    public function monthlyPotency($month)
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        $transactions = Transaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))
            ->where('type', 'general')
            ->where('poin', '>', 0)
            ->whereHas('user', function ($q) {
                $q->where('created_at', '>', $this->created_at);
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
            $user = User::find($userId);
            $sponsor = $user->sponsor;
            $i = 1;
            while ($i <= 10 && $sponsor) {
                if ($sponsor->id == $this->id) {
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
                            'description' => 'Bonus Unilevel RO dari belanja ' . $user->username . '. Belanja ' . $carts . '. Generasi ke-' . $i . ' sebesar ' . $percent . '% dari ' . $ut->poin . ' poin.',
                            'created_at' => $ut->created_at,
                        ]);
                    }
                    break;
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
            $user = User::find($userId);
            $sponsor = $user->sponsor;
            $i = 1;
            while ($i <= 10 && $sponsor) {
                if ($sponsor->id == $this->id) {
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
                            'description' => 'Bonus Unilevel RO dari belanja ' . $user->username . '. Belanja ' . $carts . '. Generasi ke-' . $i . ' sebesar ' . $percent . '% dari ' . $ut->poin . ' poin.',
                            'created_at' => $ut->created_at,
                        ]);
                    }
                    break;
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
        $ot = OfficialTransaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->whereIn('status', ['paid', 'packed', 'shipped', 'received'])->latest();
        $userIdArray = clone $ot;
        $users = $userIdArray->groupBy('user_id')->pluck('user_id');
        foreach ($users as $userId) {
            $user = User::find($userId);
            $sponsor = $user->sponsor;
            $i = 1;
            while ($i <= 10 && $sponsor) {
                if ($sponsor->id == $this->id) {
                    $percent = \App\Models\KeyValue::where('key', 'monthly_ro_unilevel_' . $i)->value('value');
                    $userTransactions = clone $ot;
                    $userTransactions = $userTransactions->where('user_id', $userId)->get();
                    foreach ($userTransactions as $ut) {
                        $potency->push([
                            'type' => 'Bonus Unilevel RO',
                            'amount' => round($ut->poin * 1000 * $percent / 100),
                            'description' => 'Bonus Unilevel RO dari belanja official ' . $user->username . '. Belanja ' . $ut->qty . ' ' . ($ut->product->name ?? 'Produk telah dihapus') . ' (' . $ut->poin . ' poin)' . '. Generasi ke-' . $i . ' sebesar ' . $percent . '% dari ' . $ut->poin . ' poin.',
                            'created_at' => $ut->created_at,
                        ]);
                    }
                    break;
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
        $users = User::whereHas('userPin', function ($q) {
            $q->whereHas('pin', function ($q_pin) {
                $q_pin->whereIn('name', ['Gold', 'Basic Upgrade Gold', 'Silver Upgrade Gold', 'Platinum', 'Basic Upgrade Platinum', 'Silver Upgrade Platinum', 'Gold Upgrade Platinum']);
            });
        })->whereHas('dailyPoins', function ($q) use ($date) {
            $q->where('pv', '>', 0);
        })->get();
        foreach ($users as $user) {
            $sponsor = $user->sponsor;
            $i = 1;
            while ($i <= 10 && $sponsor) {
                if ($sponsor->id == $this->id) {
                    $percent = \App\Models\KeyValue::where('key', 'monthly_ro_unilevel_' . $i)->value('value');
                    $dp = $user->dailyPoins()->where('pv', '>', 0)->whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->latest()->get();
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
                if ($sponsor->member->member_phase_name != 'User Free' && $sponsor->monthlyQualified($month)) {
                    $i++;
                }
                $sponsor = $sponsor->sponsor;
            }
        }
        return $potency;
    }

    public function officialTransactions()
    {
        return $this->hasMany(OfficialTransaction::class);
    }

    public function monthlyOfficial($month)
    {
        return $this->officialTransactions()->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->whereIn('status', ['paid', 'packed', 'shipped', 'received']);
    }

    public function monthlyOfficialTransactions($month)
    {
        return $this->monthlyOfficial($month)->where('is_topup', false);
    }

    public function monthlyTopupOfficialTransactions($month)
    {
        return $this->monthlyOfficial($month)->where('is_topup', true);
    }

    public function officialTransactionStockists()
    {
        return $this->hasMany(OfficialTransactionStockist::class);
    }

    public function monthlyOfficialTransactionStockists($month)
    {
        return $this->officialTransactionStockists()->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->whereIn('status', ['paid', 'packed', 'shipped', 'received']);
    }

    public function stockistOfficialTransactions()
    {
        return $this->hasMany(OfficialTransaction::class, 'stockist_id');
    }

    public function monthlyStockistOfficialTransactions($month)
    {
        return $this->stockistOfficialTransactions()->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->whereIn('status', ['paid', 'packed', 'shipped', 'received']);
    }

    public function buyPinHistories()
    {
        return $this->hasMany(PinHistory::class)->whereNull('to_id');
    }

    public function transferPinHistories()
    {
        return $this->hasMany(PinHistory::class)->whereNotNull('to_id');
    }

    public function usableUserPins()
    {
        return $this->hasMany(UserPin::class, 'buyer_id')->whereNull('user_id')->where('is_used', false)->whereHas('pin', function ($q) {
            $q->whereIn('type', ['premium', 'upgrade']);
        });
    }

    public function referrals()
    {
        $downlines = User::whereNull('id')->get();
        $this->recursive($this, $downlines);
        return $downlines;
    }

    public function freeReferrals()
    {
        $downlines = User::whereNull('id')->get();
        $this->freeRecursive($this, $downlines);
        return $downlines;
    }

    public function premiumReferrals()
    {
        $downlines = User::whereNull('id')->get();
        $this->premiumRecursive($this, $downlines);
        return $downlines;
    }

    public function agenReferrals()
    {
        $downlines = User::whereNull('id')->get();
        $this->agenRecursive($this, $downlines);
        return $downlines;
    }

    public function distributorReferrals()
    {
        $downlines = User::whereNull('id')->get();
        $this->distributorRecursive($this, $downlines);
        return $downlines;
    }

    function distributorRecursive($user, $downlines)
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

    function agenRecursive($user, $downlines)
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

    function premiumRecursive($user, $downlines)
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

    function freeRecursive($user, $downlines)
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

    function recursive($user, $downlines)
    {
        if ($user->sponsors) {
            foreach ($user->sponsors as $a) {
                $downlines->push($a);
                $this->recursive($a, $downlines);
            }
        }
        return;
    }

    public $level = 1;

    public function level($user)
    {
        $this->digLevel($user);
        return $this->level;
    }

    function digLevel($user)
    {
        if (!$user->sponsor_id) {
            return 1;
        }
        if ($user->id == $this->id) {
            return $this->level;
        }
        $this->level++;
        $this->digLevel($user->sponsor);
    }

    public function member()
    {
        return $this->hasOne(Member::class, 'member_id', 'member_id');
    }

    public function userCities()
    {
        return $this->hasMany(UserCity::class);
    }

    public function cities()
    {
        return $this->belongsToMany(City::class, 'user_cities', 'user_id', 'city_id');
    }

    public function monthlyPoin($month)
    {
        if (\App\Models\KeyValue::where('key', 'poin')->value('value') == 'enable') {
            $userPoin = $this->userPoins()->whereYear('date', date('Y', strtotime($month)))->whereMonth('date', date('m', strtotime($month)))->first();
            if ($userPoin) {
                return $userPoin->poin;
            }
        }
        // get ro poin from transaction
        $t = $this->paidTransaction($month)->sum('poin');
        // get ro poin from official transaction
        $ot = $this->monthlyOfficial($month)->sum('poin');
        // get ro poin from daily poin
        $dp = $this->monthlyDailyPoins($month)->sum('pv');
        return $t + $ot + $dp;
    }

    public function userPoins()
    {
        return $this->hasMany(UserPoin::class);
    }

    public function checkUsablePin(Pin $pin)
    {
        if (!$this->userPin && $pin->type == 'premium') {
            return true;
        }
        if (in_array($this->userPin->pin->name, ['Free Member', 'CR Reseller']) && $pin->type == 'premium') {
            return true;
        }
        if (in_array($this->userPin->pin->name, ['Basic'])) {
            if (in_array($pin->name, ['Basic Upgrade Silver', 'Basic Upgrade Gold', 'Basic Upgrade Platinum', 'Generasi', 'Generasi Up'])) {
                return true;
            }
        }
        if (in_array($this->userPin->pin->name, ['Silver', 'Basic Upgrade Silver', 'Generasi', 'Generasi Up'])) {
            if (in_array($pin->name, ['Silver Upgrade Gold', 'Silver Upgrade Platinum'])) {
                return true;
            }
        }
        if (in_array($this->userPin->pin->name, ['Gold', 'Basic Upgrade Gold', 'Silver Upgrade Gold'])) {
            if (in_array($pin->name, ['Gold Upgrade Platinum'])) {
                return true;
            }
        }
        if (!$this->userPins()->whereIn('name', ['Generasi', 'Generasi Up'])->count()) {
            if (in_array($pin->name, ['Generasi', 'Generasi Up'])) {
                return true;
            }
        }
        if (!$this->userPins()->where('name', 'like', '%BSM%')->count()) {
            if (str_contains($pin->name, 'BSM')) {
                return true;
            }
        }
        $bsm34 = ['BSM GOLD', 'BSM PLATINUM', 'BSM GOLD UP', 'BSM PLATINUM UP', 'BSM GOLD Automaintain'];
        if (!$this->userPins()->whereIn('name', $bsm34)->count()) {
            if (str_contains($pin->name, 'BSM')) {
                return true;
            }
        }
        if ($this->userPins()->whereIn('name', $bsm34)->count()) {
            if (str_contains($pin->name, 'PIN PAKET RO')) {
                return true;
            }
        }
        return false;
    }

    public function color()
    {
        $color = 'white';
        switch ($this->userPin?->pin->name_short) {
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

    // check upgradeable
    public function upgradeablePins()
    {
        $pin = explode(' ', $this->userPin->pin->name ?? '');
        $pin = end($pin);
        if (in_array($pin, ['Basic', 'Silver', 'Gold', 'Platinum'])) {
            return $this->hasMany(UserPin::class, 'buyer_id')->whereNull('user_id')->where('is_used', false)->whereHas('pin', function ($q) use ($pin) {
                $q->where('name', 'like', $pin . ' Upgrade %');
            });
        }
        return $this->hasMany(UserPin::class, 'buyer_id')->whereNull('id');
    }

    public function dailyPoins()
    {
        return $this->hasMany(DailyPoin::class);
    }

    public function monthlyDailyPoins($month)
    {
        return $this->dailyPoins()->whereYear('date', date('Y', strtotime($month)))->whereMonth('date', date('m', strtotime($month)));
    }

    public function dailyPoinSponsors()
    {
        return $this->hasManyThrough(DailyPoin::class, User::class, 'sponsor_id', 'user_id');
    }

    public function dailyBonuses($date)
    {
        return $this->bonuses()->whereIn('type', ['Komisi Pasangan', 'Bonus Generasi'])->whereDate('created_at', $date);
    }

    public function unpaidDailyBonuses($date)
    {
        return $this->bonuses()->whereIn('type', ['Komisi Pasangan', 'Bonus Generasi'])->whereDate('created_at', '<=', $date)->whereNull('paid_at');
        // if ($this->isWeekActive(Carbon::parse($date)->format('Y-\WW'))) {
        //     return $this->bonuses()->whereIn('type', ['Komisi Pasangan', 'Bonus Generasi'])->whereDate('created_at', '<=', $date)->whereNull('paid_at');
        // }
        // return $this->bonuses()->whereIn('type', ['Komisi Sponsor'])->whereDate('created_at', '<=', $date)->whereNull('paid_at');
    }

    public function dailyProfits()
    {
        return $this->hasMany(DailyProfit::class);
    }

    public function userRewards()
    {
        return $this->hasMany(UserReward::class);
    }

    public function dailyPPSponsors($date)
    {
        return $this->descendants()->whereHas('userPins', function ($q) use ($date) {
            $q->whereHas('pin', function ($q_pin) {
                $q_pin->where('poin_pair', '>', 0);
            });
            $q->where(function ($q1) {
                $q1->where('name', '!=', 'PIN PAKET RO');
                $q1->orWhere(function ($q2) {
                    $q2->where('name', 'PIN PAKET RO')->where('is_used', true);
                });
            });
            $q->whereDate('updated_at', $date);
        });
    }

    public function dailyPRSponsors($date)
    {
        return $this->descendants()->whereHas('userPin', function ($q) use ($date) {
            $q->whereHas('pin', function ($q_pin) {
                $q_pin->where('poin_reward', '>', 0);
            });
            $q->whereDate('updated_at', $date);
        });
    }

    public function generasiUserPin()
    {
        return $this->hasOne(UserPin::class)->whereHas('pin', function ($q) {
            $q->where('is_generasi', true);
        });
    }

    public function isWeekActive($week)
    {
        if ($this->userPin?->pin->level > 2 && $this->activeWeeks()->where('week', $week)->count()) {
            return true;
        }
        return false;
    }

    public function activeWeeks()
    {
        return $this->hasMany(ActiveWeek::class);
    }

    public function unpaidWeeklyBonusesAll($week)
    {
        return $this->bonuses()
            ->whereDate('created_at', '<=', Carbon::parse($week)->endofweek()->format('Y-m-d'))
            ->whereNull('paid_at')
            ->whereIn('type', ['Komisi Pasangan', 'Bonus Generasi']);
    }

    public function unpaidWeeklyBonusesSum($week)
    {
        $daily_admin_fee = 10000;
        $bonuses = $this->unpaidWeeklyBonusesAll($week)->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            })
            ->map(function ($group) use ($daily_admin_fee) {
                $group_amount = $group->sum('amount');
                return (object) [
                    'date' => $group->first()->created_at->format('Y-m-d'),
                    'amount' => $group_amount,
                    'admin' => $group_amount >= 60000 ? $daily_admin_fee : 0,
                ];
            });
        return $bonuses->sum('amount') - $bonuses->sum('admin');
    }

    public function isMonoleg()
    {
        return $this->userPins()->where('name', 'like', '%BSM%')->count();
    }
    public function monolegSponsors()
    {
        return $this->sponsors()->whereHas('userPins', function ($q) {
            $q->where('name', 'like', '%BSM%');
        });
    }

    public function daily($date)
    {
        return $this->bonuses()->whereIn('type', ['Komisi Sponsor', 'Komisi Monoleg'])->whereDate('created_at', $date);
    }

    public function unpaidDaily($date)
    {
        return $this->bonuses()->whereIn('type', ['Komisi Sponsor', 'Komisi Monoleg'])->whereDate('created_at', '<=', $date)->whereNull('paid_at');
    }

    public function monoleg(): BelongsTo
    {
        return $this->belongsTo(User::class, 'monoleg_id');
    }

    public function monolegUserPin(): HasOne
    {
        return $this->hasOne(UserPin::class)->ofMany([
            'level' => 'max',
        ], function ($q) {
            $q->where('name', 'like', '%BSM%');
        });
    }

    public function automaintains()
    {
        return $this->hasMany(Automaintain::class);
    }

    public function topups()
    {
        return $this->hasMany(Topup::class);
    }

    public function isAlreadyAutomaintain($month)
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        return $this->userPins()->whereIn('name', ['PIN PAKET RO', 'BSM GOLD Automaintain'])
            ->where('is_used', true)
            ->whereYear('created_at', $date->format('Y'))
            ->whereMonth('created_at', $date->format('m'))
            ->count();
    }

    public function userAwards()
    {
        return $this->hasMany(UserAward::class);
    }

    public function userRanks()
    {
        return $this->hasMany(UserRank::class);
    }

    public function userRank(): HasOne
    {
        return $this->hasOne(UserRank::class)->latestOfMany();
    }
}