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
use App\Services\UserService;

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

    /**
     * Get UserService instance
     */
    protected function userService()
    {
        return app(UserService::class);
    }

    public function monthlyRank($month)
    {
        return $this->userService()->monthlyRank($this, $month);
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

    public function upline()
    {
        return $this->belongsTo(User::class, 'upline_id');
    }

    public function uplines()
    {
        return $this->hasMany(User::class, 'upline_id');
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

    public function profitSharingDaily()
    {
        return $this->hasMany(ProfitSharingDaily::class);
    }

    public function powerPlusQualifications()
    {
        return $this->hasMany(PowerPlusQualification::class);
    }

    public function umrohTripSavings()
    {
        return $this->hasMany(UmrohTripSaving::class);
    }

    public function globalProfitSharingDailies()
    {
        return $this->hasMany(GlobalProfitSharingDaily::class);
    }

    public function globalProfitSharingSavings()
    {
        return $this->hasMany(GlobalProfitSharingSaving::class);
    }

    public function userTripRewards()
    {
        return $this->hasMany(UserTripReward::class);
    }

    public function umrohTripDailies()
    {
        return $this->hasMany(UmrohTripDaily::class);
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
            // Bonus Profit Sharing lama sudah dihapus, diganti dengan Bonus Global Profit Sharing
            // $q->orWhere('type', 'Bonus Profit Sharing');
            $q->orWhere('type', 'Bonus Global Profit Sharing');
            $q->orWhere('type', 'Bonus Power Plus');
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
            // Bonus Profit Sharing lama sudah dihapus, diganti dengan Bonus Global Profit Sharing
            // $q->orWhere('type', 'Bonus Profit Sharing');
            $q->orWhere('type', 'Bonus Global Profit Sharing');
            $q->orWhere('type', 'Bonus Power Plus');
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

    public function monthlyProfitSharingBonuses($month)
    {
        return $this->bonuses()->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->where(function ($q) {
            $q->where('type', 'Bonus Profit Sharing');
        });
    }

    public function dailyProfitSharingHistory($month)
    {
        $startDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        
        return $this->profitSharingDaily()
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'desc');
    }

    public function monthlyPowerPlusBonuses($month)
    {
        return $this->bonuses()->whereYear('created_at', date('Y', strtotime($month)))->whereMonth('created_at', date('m', strtotime($month)))->where(function ($q) {
            $q->where('type', 'Bonus Power Plus');
        });
    }

    public function monthlyQualified($month)
    {
        return $this->userService()->monthlyQualified($this, $month);
    }

    public function monthlyRoyaltyQualified($month)
    {
        return $this->userService()->monthlyRoyaltyQualified($this, $month);
    }

    public function monthlyPotency($month)
    {
        return $this->userService()->monthlyPotency($this, $month);
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
        return $this->userService()->referrals($this);
    }

    public function freeReferrals()
    {
        return $this->userService()->freeReferrals($this);
    }

    public function premiumReferrals()
    {
        return $this->userService()->premiumReferrals($this);
    }

    public function agenReferrals()
    {
        return $this->userService()->agenReferrals($this);
    }

    public function distributorReferrals()
    {
        return $this->userService()->distributorReferrals($this);
    }

    public function level($user)
    {
        return $this->userService()->level($this, $user);
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
        return $this->userService()->monthlyPoin($this, $month);
    }

    public function userPoins()
    {
        return $this->hasMany(UserPoin::class);
    }

    public function checkUsablePin(Pin $pin)
    {
        return $this->userService()->checkUsablePin($this, $pin);
    }

    public function color()
    {
        return $this->userService()->color($this);
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

    public function dailyPoinUplines()
    {
        return $this->hasManyThrough(DailyPoin::class, User::class, 'upline_id', 'user_id');
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
            // Exclude pin RO (is_ro = true) kecuali yang sudah is_used
            $q->where(function ($q1) {
                $q1->where('is_ro', false)
                    ->orWhere(function ($q2) {
                        $q2->where('is_ro', true)->where('is_used', true);
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
        return $this->userService()->isWeekActive($this, $week);
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
        return $this->userService()->isMonoleg($this);
    }

    public function monolegSponsors()
    {
        return $this->uplines();
    }

    public function daily($date)
    {
        return $this->bonuses()->whereIn('type', ['Komisi Sponsor', 'Komisi Monoleg', 'Bonus Generasi'])->whereDate('created_at', $date);
    }

    public function unpaidDaily($date)
    {
        return $this->bonuses()->whereIn('type', ['Komisi Sponsor', 'Komisi Monoleg', 'Bonus Generasi'])->whereDate('created_at', '<=', $date)->whereNull('paid_at');
    }

    public function monoleg(): BelongsTo
    {
        return $this->belongsTo(User::class, 'monoleg_id');
    }

    public function monolegUserPin(): HasOne
    {
        return $this->hasOne(UserPin::class)->ofMany([
            'level' => 'max',
        ]);
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
        return $this->userService()->isAlreadyAutomaintain($this, $month);
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