<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use DB;
use App\Models\Pin;
use App\Models\User;
use App\Models\Award;
use App\Models\Bonus;
use App\Traits\Helper;
use App\Models\UserPin;
use App\Models\PinHistory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class HelperController extends Controller
{
    public function award()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        if (!Schema::hasColumn('users', 'cash_award')) {
            Schema::table('users', function (Blueprint $table) {
                $table->bigInteger('cash_award')->default(0);
                $table->bigInteger('cash_rank')->default(0);
            });
        }

        $users = User::where('cash_award', '>', 0)->orWhere('cash_rank', '>', 0)->get();
        foreach ($users as $a) {
            $a->cash_award = 0;
            $a->cash_rank = 0;
            $a->timestamps = false;
            $a->save();
        }

        Award::firstOrCreate([
            'nominal' => 1000000,
            'award' => 'CASH',
        ]);
        Award::firstOrCreate([
            'nominal' => 5000000,
            'award' => 'HP',
        ]);
        Award::firstOrCreate([
            'nominal' => 15000000,
            'award' => 'DANA MOTOR',
        ]);
        Award::firstOrCreate([
            'nominal' => 50000000,
            'award' => 'UMROH/HOLY LAND',
        ]);
        Award::firstOrCreate([
            'nominal' => 100000000,
            'award' => 'DANA MOBIL',
        ]);
        Award::firstOrCreate([
            'nominal' => 250000000,
            'award' => 'DANA MOBIL',
        ]);
        Award::firstOrCreate([
            'nominal' => 500000000,
            'award' => 'DANA MOBIL',
        ]);
        Award::firstOrCreate([
            'nominal' => 1000000000,
            'award' => 'RUMAH',
        ]);
        Rank::firstOrCreate([
            'nominal' => 5000000,
            'rank' => '★',
        ]);
        Rank::firstOrCreate([
            'nominal' => 20000000,
            'rank' => '★★',
        ]);
        Rank::firstOrCreate([
            'nominal' => 50000000,
            'rank' => '★★★',
        ]);
        Rank::firstOrCreate([
            'nominal' => 100000000,
            'rank' => '★★★★',
        ]);
        Rank::firstOrCreate([
            'nominal' => 250000000,
            'rank' => '★★★★★',
        ]);
        Rank::firstOrCreate([
            'nominal' => 500000000,
            'rank' => '★★★★★★',
        ]);
        Rank::firstOrCreate([
            'nominal' => 1000000000,
            'rank' => '★★★★★★★',
        ]);
        Rank::firstOrCreate([
            'nominal' => 1500000000,
            'rank' => 'DIAMOND',
        ]);
        Rank::firstOrCreate([
            'nominal' => 5000000000,
            'rank' => 'CROWN',
        ]);
        Rank::firstOrCreate([
            'nominal' => 10000000000,
            'rank' => 'SENIOR CROWN',
        ]);
        Rank::firstOrCreate([
            'nominal' => 20000000000,
            'rank' => 'ROYAL CROWN',
        ]);
        $bonuses = Bonus::whereDate('created_at', '>=', '2023-06-04')
            ->whereIn('type', ['Komisi Sponsor', 'Komisi Monoleg', 'Komisi Pasangan', 'Bonus Generasi'])
            ->get();
        foreach ($bonuses as $a) {
            $user = $a->user;
            $user->cash_rank += $a->amount;
            $user->timestamps = false;
            $user->save();
        }
    }

    public function monoleg()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        if (!Schema::hasColumn('pins', 'bonus_monoleg')) {
            Schema::table('pins', function (Blueprint $table) {
                $table->bigInteger('bonus_monoleg')->default(0);
                $table->boolean('is_generasi')->default(false);
            });
        }

        if (!Schema::hasColumn('users', 'monoleg_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->bigInteger('monoleg_id')->unsigned()->nullable();
                $table->foreign('monoleg_id')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('daily_profits', 'pp_id')) {
            Schema::table('daily_profits', function (Blueprint $table) {
                $table->bigInteger('pp_id')->unsigned()->nullable();
                $table->foreign('pp_id')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('daily_profits', 'pr_id')) {
            Schema::table('daily_profits', function (Blueprint $table) {
                $table->bigInteger('pr_id')->unsigned()->nullable();
                $table->foreign('pr_id')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('set null');
            });
        }

        \App\Models\Pin::updateOrCreate(
            [
                'name' => 'Generasi',
            ],
            [
                'is_generasi' => true,
            ]
        );

        \App\Models\Pin::updateOrCreate(
            [
                'name' => 'Generasi Up',
            ],
            [
                'is_generasi' => true,
                'level' => 2,
            ]
        );

        \App\Models\Pin::firstOrCreate(
            [
                'name' => 'BSM SILVER',
            ],
            [
                'type' => 'premium',
                'price' => 375000,
                'bonus_sponsor' => 50000,
                'bonus_monoleg' => 50000,
                'is_generasi' => true,
                'level' => 2,
            ]
        );
        \App\Models\Pin::firstOrCreate(
            [
                'name' => 'BSM GOLD',
            ],
            [
                'type' => 'premium',
                'price' => 2000000,
                'bonus_sponsor' => 240000,
                'bonus_monoleg' => 500000,
                'poin_pair' => 4,
                'is_generasi' => true,
                'level' => 3,
            ]
        );
        \App\Models\Pin::firstOrCreate(
            [
                'name' => 'BSM PLATINUM',
            ],
            [
                'type' => 'premium',
                'price' => 6000000,
                'bonus_sponsor' => 600000,
                'bonus_monoleg' => 500000,
                'poin_pair' => 4,
                'is_generasi' => true,
                'level' => 4,
            ]
        );
        \App\Models\Pin::firstOrCreate(
            [
                'name' => 'BSM SILVER UP',
            ],
            [
                'type' => 'upgrade',
                'is_generasi' => true,
                'level' => 2,
            ]
        );
        \App\Models\Pin::firstOrCreate(
            [
                'name' => 'BSM GOLD UP',
            ],
            [
                'type' => 'upgrade',
                'is_generasi' => true,
                'level' => 3,
            ]
        );
        \App\Models\Pin::firstOrCreate(
            [
                'name' => 'BSM PLATINUM UP',
            ],
            [
                'type' => 'upgrade',
                'is_generasi' => true,
                'level' => 4,
            ]
        );
        \App\Models\Pin::firstOrCreate(
            [
                'name' => 'PIN PAKET RO',
            ],
            [
                'type' => 'ro',
                'price' => 2000000,
                'bonus_monoleg' => 500000,
                'poin_pair' => 4,
                'poin_reward' => 3,
                'is_generasi' => true,
                'level' => 3,
            ]
        );
        \App\Models\Pin::firstOrCreate(
            [
                'name' => 'BSM GOLD Automaintain',
            ],
            [
                'type' => 'premium',
                'price' => 2000000,
                'bonus_monoleg' => 500000,
                'poin_pair' => 4,
                'poin_reward' => 3,
                'is_generasi' => true,
                'level' => 3,
            ]
        );

        // bonus generasi silver > bonus generasi
        $bonuses = Bonus::where('type', 'Bonus Generasi Silver')->get();
        foreach ($bonuses as $a) {
            $a->type = 'Bonus Generasi';
            $a->description = str_replace("Bonus Generasi Silver", "Bonus Generasi", $a->description);
            $a->timestamps = false;
            $a->save();
        }

        if (!Schema::hasColumn('users', 'cash_automaintain')) {
            Schema::table('users', function (Blueprint $table) {
                $table->bigInteger('cash_automaintain')->default(0);
            });
        }

        $users = User::whereHas('userPin', function ($q) {
            $q->whereNotIn('name', ['Free Member', 'CR Reseller', 'Basic'])
                ->whereDate('updated_at', '>=', '2022-08-07');
        })->with('userPin')->get();
        $count = $users->count();
        foreach ($users->sortBy('userPin.updated_at')->values() as $key => $a) {
            $pin = null;
            if ($a->userPin->pin->name_short == 'Silver') {
                $pin = Pin::where('name', 'BSM SILVER UP')->first();
            } elseif ($a->userPin->pin->name_short == 'Gold') {
                $pin = Pin::where('name', 'BSM GOLD UP')->first();
            } elseif ($a->userPin->pin->name_short == 'Platinum') {
                $pin = Pin::where('name', 'BSM PLATINUM UP')->first();
            } else {
                print('user pin ' . $a->userPin->pin->name_short . ' not found >>> ');
            }
            if ($pin) {
                $userPin = $a->userPins()->create([
                    'buyer_id' => $a->id,
                    'pin_id' => $pin->id,
                    'code' => strtoupper(str_random(6)),
                    'name' => $pin->name,
                    'price' => $pin->price,
                    'level' => $pin->level,
                ]);
                Helper::pinHistory($userPin);
                Helper::upgrade($userPin);
            }
            print($key + 1 . ' / ' . $count . ' | ' . $a->username);
            print('<br>');
        }
    }

    public function level()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        if (!Schema::hasColumn('user_pins', 'level')) {
            Schema::table('user_pins', function (Blueprint $table) {
                $table->integer('level')->default(0);
            });
        }

        $userPins = UserPin::whereNotIn('name', ['Free Member', 'CR Reseller'])->get();
        $count = $userPins->count();
        foreach ($userPins as $key => $a) {
            $a->level = $a->pin->level;
            $a->timestamps = false;
            $a->save();
            print($key + 1 . ' / ' . $count . ' | ' . $a->code);
            print('<br>');
        }

        return 'success!';
    }

    public function generasiUp()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        \App\Models\Pin::firstOrCreate([
            'name' => 'Generasi Up',
        ], [
                'type' => 'upgrade',
                'price' => 0,
                'bonus_sponsor' => 0,
                'poin_pair' => 0,
                'poin_reward' => 0,
                'poin_ro' => 0,
                'pair_flush' => 0,
            ]);
    }
    public function sync()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        $member = \App\Models\Member::where('member_username', request()->username)->first();
        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'username not found!',
            ], 400);
        }
        // clone from register api
        if (!$member->member_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_id is required',
            ], 400);
        }
        $idCount = User::where('member_id', $member->member_id)->count();
        if ($idCount) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_id is already in use',
            ], 400);
        }
        if (!$member->member_username) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_username is required',
            ], 400);
        }
        $usernameCount = User::where('username', $member->member_username)->count();
        if ($usernameCount) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_username is already in use',
            ], 400);
        }
        if (!$member->member_email) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_email is required',
            ], 400);
        }
        if (!$member->member_password) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_password is required',
            ], 400);
        }
        if (!$member->member_phase_name) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_phase_name is required',
            ], 400);
        }
        if (!$member->member_sponsor_member_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_sponsor_member_id is required',
            ], 400);
        }
        // add to user table
        $user = User::create([
            'name' => $member->member_name,
            'email' => $member->member_email,
            'password' => $member->member_password,
            'type' => 'member',
            'username' => $member->member_username,
            'phone' => $member->member_phone,
            'ktp' => $member->member_identity_number,
            'npwp' => $member->member_npwp,
            'member_id' => $member->member_id,
            'phase' => $member->member_phase_name,

            'updated_at' => $member->member_datetime,
            'created_at' => $member->member_datetime,
            'sponsor_id' => User::where('member_id', $member->member_sponsor_member_id)->value('id'),
            'is_stockist' => $member->is_stockist ?? 0,
        ]);
        // add pin for member
        if ($member->member_phase_name == 'User Free') {
            $user->userPin()->create([
                'pin_id' => \App\Models\Pin::where('name', 'Free Member')->value('id'),
                'name' => 'Free Member',
                'code' => strtoupper(str_random(6)),
                'price' => 0,
            ]);
        } else {
            $user->userPin()->create([
                'pin_id' => \App\Models\Pin::where('name', 'CR Reseller')->value('id'),
                'name' => 'CR Reseller',
                'code' => strtoupper(str_random(6)),
                'price' => 150000,
            ]);
        }
        // add bank account
        if ($member->member_bank_name) {
            $bank = \App\Models\Bank::where('name', 'like', '%' . $member->member_bank_name . '%')->first();
            if (!$bank) {
                $bank = \App\Models\Bank::create([
                    'name' => strtoupper($member->member_bank_name),
                ]);
            }
            $user->update([
                'bank_id' => $bank->id,
                'bank_account' => $member->member_bank_account_number,
                'bank_as' => $member->member_bank_account_name,
            ]);
        }
        // add address
        if ($member->member_province_id && $member->member_city_id && $member->member_subdistrict_id) {
            $user->addresses()->create([
                'is_active' => true,
                'name' => 'Rumah',
                'email' => $member->member_email,
                'address' => $member->member_address,
                'phone' => $member->member_phone,
                'province_id' => $member->member_province_id,
                'city_id' => $member->member_city_id,
                'subdistrict_id' => $member->member_subdistrict_id,
            ]);
        }
        return $user;
    }

    public function member()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        return \App\Models\Member::where('member_username', request()->username)->first();
    }

    public function tokenable()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
    }

    public function bonus()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        if (!Schema::hasColumn('bonuses', 'paid_at')) {
            Schema::table('bonuses', function (Blueprint $table) {
                $table->datetime('paid_at')->nullable();
                $table->datetime('used_at')->nullable();
                $table->bigInteger('used_amount')->nullable();
            });
        }

        if (Schema::hasColumn('bonuses', 'is_paid')) {
            $bonuses = \App\Models\Bonus::where('is_paid', true)->get();
        } else {
            $bonuses = \App\Models\Bonus::whereNotNull('paid_at')->get();
        }
        foreach ($bonuses as $bonus) {
            $bonus->timestamps = false;
            $bonus->paid_at = $bonus->updated_at;
            $bonus->save();
        }

        if (Schema::hasColumn('bonuses', 'is_paid')) {
            Schema::table('bonuses', function (Blueprint $table) {
                $table->dropColumn('is_paid');
            });
        }
    }

    public function syaratMingguan()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        if (!Schema::hasColumn('products', 'is_weekly')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_weekly')->default(false);
            });
        }
    }

    public function generasi()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        \App\Models\Pin::firstOrCreate([
            'name' => 'Generasi',
        ], [
                'type' => 'upgrade',
                'price' => 275000,
                'bonus_sponsor' => 50000,
                'poin_pair' => 0,
                'poin_reward' => 0,
                'poin_ro' => 0,
                'pair_flush' => 0,
            ]);

        for ($i = 1; $i <= 10; $i++) {
            $value = 0;
            if (in_array($i, [1, 5])) {
                $value = 10000;
            } elseif (in_array($i, [2, 4])) {
                $value = 20000;
            } elseif ($i == 3) {
                $value = 30000;
            } else {
                $value = 2000;
            }
            \App\Models\KeyValue::where('key', 'weekly_unilevel_' . $i)->first()->update(['value' => $value]);
        }

        if (!Schema::hasColumn('pins', 'level')) {
            Schema::table('pins', function (Blueprint $table) {
                $table->integer('level')->default(0);
            });
        }
        \App\Models\Pin::whereIn('name', ['Free Member', 'CR Reseller'])->update(['level' => 0]);
        \App\Models\Pin::where('name', 'Basic')->update(['level' => 1]);
        \App\Models\Pin::where('name', 'like', '%Silver')->update(['level' => 2]);
        \App\Models\Pin::where('name', 'like', '%Gold')->update(['level' => 3]);
        \App\Models\Pin::where('name', 'like', '%Platinum')->update(['level' => 4]);
        \App\Models\Pin::where('name', 'Basic Upgrade Gold')->update(['poin_ro' => 150]);
        \App\Models\Pin::where('name', 'Gold Upgrade Platinum')->update(['poin_ro' => 150]);
    }

    public function pair()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        $date = '2022-08-07';
        // delete komisi sponsor
        \App\Models\Bonus::where('type', 'Komisi Sponsor')->whereDate('created_at', $date)->delete();
        // delete dailyPoins
        \App\Models\DailyPoin::whereDate('date', $date)->delete();
        // regenerate pair helper
        $userPins = \App\Models\UserPin::whereHas('pin', function ($q) {
            $q->whereIn('name', ['Gold', 'Basic Upgrade Gold', 'Silver Upgrade Gold', 'Platinum', 'Basic Upgrade Platinum', 'Silver Upgrade Platinum', 'Gold Upgrade Platinum']);
        })->whereNotNull('user_id')->whereDate('updated_at', $date)->oldest()->get();
        foreach ($userPins as $a) {
            \App\Models\Traits\Helper::upgrade($a);
        }
    }

    public function reward()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (Schema::hasColumn('rewards', 'pr')) {
            Schema::table('rewards', function (Blueprint $table) {
                $table->dropColumn('pr');
                $table->boolean('is_platinum')->default(false);
            });
        }

        DB::statement("SET foreign_key_checks=0");
        \App\Models\Reward::truncate();
        \App\Models\DailyProfit::truncate();
        DB::statement("SET foreign_key_checks=1");

        \App\Models\Reward::create(['nominal' => 400000, 'reward' => 'Emas Batangan 0.3g']);
        \App\Models\Reward::create(['nominal' => 1000000, 'reward' => 'Emas Batangan 1g']);
        \App\Models\Reward::create(['nominal' => 3000000, 'reward' => 'HP Android']);
        \App\Models\Reward::create(['nominal' => 10000000, 'reward' => 'Laptop']);
        \App\Models\Reward::create(['nominal' => 25000000, 'reward' => 'Sepeda Motor']);
        \App\Models\Reward::create(['nominal' => 50000000, 'reward' => 'Paket Wisata Religi']);
        \App\Models\Reward::create(['nominal' => 200000000, 'reward' => 'Honda Brio']);
        \App\Models\Reward::create(['nominal' => 500000000, 'reward' => 'Pajero', 'is_platinum' => true]);
        \App\Models\Reward::create(['nominal' => 1500000000, 'reward' => 'Rumah', 'is_platinum' => true]);
        \App\Models\Reward::create(['nominal' => 5000000000, 'reward' => 'Rumah Mewah', 'is_platinum' => true]);
        \App\Models\Reward::create(['nominal' => 10000000000, 'reward' => 'Uang Cash', 'is_platinum' => true]);

        foreach (\App\Models\DailyPoin::all() as $a) {
            if ($a->pp >= $a->pr) {
                $a->update([
                    'pr' => ($a->pp - $a->pr) + ($a->pr * 4),
                ]);
            }
        }

        \App\Models\Bonus::where('type', 'Komisi Pasangan')->delete();

        if (!Schema::hasColumn('users', 'cash_reward')) {
            Schema::table('users', function (Blueprint $table) {
                $table->bigInteger('cash_reward')->default(0);
            });
        }
        return 'success';
    }

    public function plana()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        $users = \App\Models\User::whereHas('userPin', function ($q) {
            $q->whereHas('pin', function ($q_pin) {
                $q_pin->where('name', 'CR Reseller');
            });
        })->get();
        $pinFree = \App\Models\Pin::where('name', 'Free Member')->first();
        $pin = \App\Models\Pin::where('name', 'Basic')->first();
        foreach ($users as $a) {
            $a->userPin->update([
                'pin_id' => $pin->id,
                'name' => $pin->name,
                'price' => $pin->price,
            ]);
        }
        \App\Models\UserPin::where('name', 'CR Reseller')->delete();

        $users = \App\Models\User::whereDoesntHave('userPin')->get();
        foreach ($users as $a) {
            if ($a->phase == 'User Free') {
                $a->userPin()->create([
                    'pin_id' => $pinFree->id,
                    'code' => strtoupper(str_random(6)),
                    'name' => $pinFree->name,
                    'price' => $pinFree->price,
                    'level' => $pinFree->level,
                ]);
            } else {
                $a->userPin()->create([
                    'pin_id' => $pin->id,
                    'code' => strtoupper(str_random(6)),
                    'name' => $pin->name,
                    'price' => $pin->price,
                    'level' => $pin->level,
                ]);
            }
        }
        return 'success';
    }

    public function phase()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (!Schema::hasColumn('users', 'phase')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phase')->nullable();
            });
        }
        $members = \App\Models\Member::all();
        foreach ($members as $a) {
            $user = \App\Models\User::where('username', $a->member_username)->first();
            if ($user) {
                $user->update([
                    'phase' => $a->member_phase_name,
                ]);
            }
        }
        return 'success!';
    }

    public function product()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (!Schema::hasColumn('users', 'member_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->bigInteger('member_id')->nullable();
            });
        }
        foreach (\App\Models\User::where('type', '!=', 'admin')->get() as $a) {
            $a->update([
                'member_id' => $a->image,
            ]);
            $a->save();
            $a->update([
                'image' => null,
            ]);
        }
        if (!Schema::hasColumn('products', 'youtube')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('youtube')->nullable();
                $table->bigInteger('price_master')->nullable();
            });
        }
        return 'success!';
    }

    public function news()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (!Schema::hasColumn('news', 'youtube')) {
            Schema::table('news', function (Blueprint $table) {
                $table->string('youtube')->nullable();
                $table->string('file')->nullable();
            });
        }
        return 'success!';
    }

    public function masterStockist()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (!Schema::hasColumn('users', 'is_master_stockist')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_master_stockist')->default(false);
            });
        }
        if (!Schema::hasColumn('official_transaction_stockists', 'is_master')) {
            Schema::table('official_transaction_stockists', function (Blueprint $table) {
                $table->boolean('is_master')->default(false);
            });
        }
        if (!Schema::hasColumn('transactions', 'type')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('type')->default('general'); // general, stockist, masterstockist
            });
        }
        if (!Schema::hasColumn('transactions', 'master_stockist_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->bigInteger('master_stockist_id')->unsigned()->nullable();
                $table->foreign('master_stockist_id')->references('id')->on('users')
                    ->onUpdate('cascade')->onDelete('set null');
            });
        }
        return 'success!';
    }

    public function isTurbo()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (!Schema::hasColumn('official_transactions', 'is_turbo')) {
            Schema::table('official_transactions', function (Blueprint $table) {
                $table->boolean('is_turbo')->default(false);
            });
        }
        return 'success!';
    }

    public function priceStockist()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (!Schema::hasColumn('products', 'price_stockist')) {
            Schema::table('products', function (Blueprint $table) {
                $table->bigInteger('price_stockist')->nullable();
            });
        }
        return 'success!';
    }

    public function isHidden()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (!Schema::hasColumn('products', 'is_hidden')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_hidden')->default(false);
            });
        }
        return 'success!';
    }

    public function recipient()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (!Schema::hasColumn('addresses', 'recipient')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->string('recipient')->nullable();
            });
        }
        return 'success!';
    }

    public function roles()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (!Schema::hasColumn('users', 'roles')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('roles')->nullable();
            });
        }
        return 'success!';
    }

    public function address()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        $users = \App\Models\User::where('type', 'member')->whereDoesntHave('address')->get();
        foreach ($users as $user) {
            $member = $user->member;
            if (!$member)
                break;
            print($user->username);
            if ($member->member_subdistrict_id && $member->member_city_id && $member->member_province_id) {
                $user->addresses()->create([
                    'is_active' => true,
                    'name' => 'Rumah',
                    'email' => $member->member_email,
                    'address' => $member->member_address,
                    'phone' => $member->member_phone,
                    'province_id' => $member->member_province_id,
                    'city_id' => $member->member_city_id,
                    'subdistrict_id' => $member->member_subdistrict_id,
                ]);
                print(' added!');
            }
            print('<br>');
        }
        return 'success!';
    }

    public function isBig()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (!Schema::hasColumn('products', 'is_big')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_big')->default(false);
            });
        }
        if (!Schema::hasColumn('official_transactions', 'is_big')) {
            Schema::table('official_transactions', function (Blueprint $table) {
                $table->boolean('is_big')->default(false);
                $table->integer('month')->nullable();
                $table->integer('month_key')->nullable();
            });
        }
        if (!Schema::hasColumn('official_transactions', 'address_id')) {
            Schema::table('official_transactions', function (Blueprint $table) {
                $table->bigInteger('address_id')->unsigned()->nullable();
                $table->foreign('address_id')->references('id')->on('addresses')
                    ->onUpdate('cascade')->onDelete('set null');
            });
        }
        if (!Schema::hasColumn('official_transactions', 'shipment_number')) {
            Schema::table('official_transactions', function (Blueprint $table) {
                $table->string('shipment_number')->nullable();
                $table->bigInteger('shipment_fee')->nullable();
                $table->integer('weight')->nullable();
                $table->integer('code')->nullable();
                $table->bigInteger('price_total')->nullable();
                $table->string('receipt')->nullable();
                $table->string('status')->default('pending'); // expired, paid, packed, shipped, received
            });
        }
        if (!Schema::hasColumn('official_transactions', 'product_name')) {
            Schema::table('official_transactions', function (Blueprint $table) {
                $table->string('product_name')->nullable();
            });
        }
        if (!Schema::hasColumn('official_transactions', 'product_price')) {
            Schema::table('official_transactions', function (Blueprint $table) {
                $table->bigInteger('product_price')->nullable();
            });
        }
        if (!Schema::hasColumn('official_transactions', 'shipment')) {
            Schema::table('official_transactions', function (Blueprint $table) {
                $table->string('shipment')->nullable();
            });
        }
        if (!Schema::hasColumn('official_transactions', 'official_transaction_id')) {
            Schema::table('official_transactions', function (Blueprint $table) {
                $table->bigInteger('official_transaction_id')->unsigned()->nullable();
                $table->foreign('official_transaction_id')->references('id')->on('official_transactions')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }
        foreach (\App\Models\OfficialTransaction::all() as $a) {
            $a->update([
                'status' => 'received',
                'product_name' => $a->product->name,
                'product_price' => $a->qty ? ($a->price / $a->qty) : 0,
                'price_total' => $a->price,
            ]);
        }

        return 'success!';
    }

    public function profitSharing13()
    {

    }

    public function productMonth()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (!Schema::hasColumn('products', 'month')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('month')->nullable();
            });
        }
        return 'success!';
    }

    public function bigTransaction()
    {
        if (!Schema::hasColumn('official_transactions', 'transaction_id')) {
            Schema::table('official_transactions', function (Blueprint $table) {
                $table->bigInteger('transaction_id')->unsigned()->nullable();
                $table->foreign('transaction_id')->references('id')->on('transactions')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        }
        return 'success!';
    }

    public function pin()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        if (!Schema::hasColumn('pins', 'bonus_sponsor')) {
            Schema::table('pins', function (Blueprint $table) {
                $table->bigInteger('bonus_sponsor')->default(0);
                $table->integer('poin_pair')->default(0);
                $table->integer('poin_reward')->default(0);
                $table->integer('poin_ro')->default(0);
                $table->integer('pair_flush')->default(0);
                $table->integer('reward_flush')->default(0);
            });
        }
        if (!\App\Models\Pin::where('name', 'Basic')->get()->count()) {
            \App\Models\Pin::create([
                'name' => 'Basic',
                'type' => 'premium',
                'price' => 175000,
                'bonus_sponsor' => 0,
                'poin_pair' => 0,
                'poin_reward' => 0,
                'poin_ro' => 0,
                'pair_flush' => 0,
            ]);
            \App\Models\Pin::create([
                'name' => 'Silver',
                'type' => 'premium',
                'price' => 250000,
                'bonus_sponsor' => 50000,
                'poin_pair' => 0,
                'poin_reward' => 0,
                'poin_ro' => 0,
                'pair_flush' => 0,
            ]);
            \App\Models\Pin::create([
                'name' => 'Gold',
                'type' => 'premium',
                'price' => 950000,
                'bonus_sponsor' => 200000,
                'poin_pair' => 1,
                'poin_reward' => 1,
                'poin_ro' => 40,
                'pair_flush' => 15,
                'reward_flush' => 15,
            ]);
            \App\Models\Pin::create([
                'name' => 'Platinum',
                'type' => 'premium',
                'price' => 2900000,
                'bonus_sponsor' => 400000,
                'poin_pair' => 1,
                'poin_reward' => 4,
                'poin_ro' => 250,
                'pair_flush' => 30,
                'reward_flush' => 30,
            ]);
            \App\Models\Pin::create([
                'name' => 'Basic Upgrade Silver',
                'type' => 'upgrade',
                'price' => 100000,
                'bonus_sponsor' => 50000,
                'poin_pair' => 0,
                'poin_reward' => 0,
                'poin_ro' => 0,
                'pair_flush' => 0,
            ]);
            \App\Models\Pin::create([
                'name' => 'Basic Upgrade Gold',
                'type' => 'upgrade',
                'price' => 775000,
                'bonus_sponsor' => 200000,
                'poin_pair' => 1,
                'poin_reward' => 1,
                'poin_ro' => 40,
                'pair_flush' => 15,
                'reward_flush' => 15,
            ]);
            \App\Models\Pin::create([
                'name' => 'Basic Upgrade Platinum',
                'type' => 'upgrade',
                'price' => 2725000,
                'bonus_sponsor' => 400000,
                'poin_pair' => 1,
                'poin_reward' => 4,
                'poin_ro' => 250,
                'pair_flush' => 30,
                'reward_flush' => 30,
            ]);
            \App\Models\Pin::create([
                'name' => 'Silver Upgrade Gold',
                'type' => 'upgrade',
                'price' => 700000,
                'bonus_sponsor' => 150000,
                'poin_pair' => 1,
                'poin_reward' => 1,
                'poin_ro' => 40,
                'pair_flush' => 15,
                'reward_flush' => 15,
            ]);
            \App\Models\Pin::create([
                'name' => 'Silver Upgrade Platinum',
                'type' => 'upgrade',
                'price' => 2650000,
                'bonus_sponsor' => 350000,
                'poin_pair' => 1,
                'poin_reward' => 4,
                'poin_ro' => 250,
                'pair_flush' => 30,
                'reward_flush' => 30,
            ]);
            \App\Models\Pin::create([
                'name' => 'Gold Upgrade Platinum',
                'type' => 'upgrade',
                'price' => 1950000,
                'bonus_sponsor' => 200000,
                'poin_pair' => 0,
                'poin_reward' => 3,
                'poin_ro' => 210,
                'pair_flush' => 30,
                'reward_flush' => 30,
            ]);
        }
        return 'success!';
    }
}