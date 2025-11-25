<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\AboutUs;
use App\Models\Blog;
use DB;
use App\Models\Member;
use App\Models\User;
use App\Models\Pin;
use App\Models\Bank;
use Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Session;
use App\Models\Transaction;
use App\Models\OfficialTransaction;
use DateTime;
use App\Models\UserPoin;
use App\Models\Poin;
use App\Traits\Helper;
use App\Models\Bonus;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (env('CR_ID_REDIRECT')) {
            if (strpos(url(''), 'campreseller.co.id') !== false) {
                return redirect(env('CR_ID_REDIRECT'));
            }
        }
        $categories = Category::orderBy('name')->limit(4)->get();
        $products = Product::where('is_hidden', false);
        if (Auth::guest() || session('mode') == 'stockist') {
            $products = $products->where('is_big', false);
        }
        $products = $products->orderBy('created_at', 'DESC')->limit(12)->get();
        $about_us = AboutUs::first();
        $blogs = Blog::orderBy('created_at', 'DESC')->limit(4)->get();
        return view('shop.index', compact('categories', 'products', 'about_us', 'blogs'));
    }

    public function tree()
    {
        $memberPhases = Auth::user()->member->memberPhases()->where('member_phase_name', 'User Q')->orderBy('member_phase_count')->get();
        $rootChildrens = $memberPhases->map(function ($memberPhase) {
            $arrayMemberPhase['name'] = $memberPhase->member_phase_name . ' - ' . $memberPhase->member_phase_count;
            $arrayMemberPhase['children'] = $memberPhase->memberPhaseDetails()->orderBy('member_phase_detail_position')->get()->map(function ($memberPhaseDetail) {
                $arrayMemberPhaseDetail['name'] = $memberPhaseDetail->member->member_username;
                return $arrayMemberPhaseDetail;
            })->toArray();
            return $arrayMemberPhase;
        })->toArray();
        $treeJson = json_encode([
            'name' => Auth::user()->username,
            'children' => $rootChildrens,
        ], JSON_PRETTY_PRINT);
        file_put_contents(storage_path('tree/' . Auth::user()->username . '.json'), stripslashes($treeJson));
        return view('tree.orgchart');
    }

    public function phase($username, $phase)
    {
        $phaseDB = $phase;
        if ($phase == 'Star Seller') {
            $phaseDB = 'Start Seller';
        }
        $memberPhases = \App\Models\User::where('username', $username)->first()->member->memberPhases()->where('member_phase_name', $phaseDB)->orderBy('member_phase_count')->get();
        $rootChildrens = $memberPhases->map(function ($memberPhase) {
            $arrayMemberPhase['name'] = $memberPhase->member_phase_name . ' - ' . $memberPhase->member_phase_count;
            $arrayMemberPhase['children'] = $memberPhase->memberPhaseDetails()->orderBy('member_phase_detail_position')->get()->map(function ($memberPhaseDetail) {
                $arrayMemberPhaseDetail['name'] = $memberPhaseDetail->member->member_username;
                return $arrayMemberPhaseDetail;
            })->toArray();
            return $arrayMemberPhase;
        })->toArray();
        $treeJson = json_encode([
            'name' => Auth::user()->username,
            'children' => $rootChildrens,
        ], JSON_PRETTY_PRINT);
        // Storage::disk('public')->put('tree/'.Auth::user()->username.'-'.$phase.'.json', $treeJson);
        return $treeJson;
    }

    public function mysql2()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        // add new pin
        Pin::updateOrCreate([
            'name' => 'CR Reseller',
        ], [
                'type' => 'premium',
                'price' => 150000,
            ]);

        User::where('id', '>', 2)->delete();

        $members = Member::orderBy('member_datetime', 'asc')->get();
        foreach ($members as $key => $a) {
            // add to user table
            $user = User::create([
                'name' => $a->member_name,
                'email' => $a->member_email,
                'password' => $a->member_password,
                'type' => 'member',
                'username' => $a->member_username,
                'phone' => $a->member_phone,
                'ktp' => $a->member_identity_number,
                'npwp' => $a->member_npwp,
                'image' => $a->member_id,
                'created_at' => $a->member_datetime,
            ]);
            // add pin for member
            if ($a->member_phase_name == 'User Free') {
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
            // add sponsor
            $network = $a->memberNetworks()->first();
            if ($network) {
                $sponsor_id = User::where('image', $network->sponsor->member_id)->value('id');
            } else {
                $sponsor_id = User::where('type', 'admin')->value('id');
            }
            $user->update([
                'sponsor_id' => $sponsor_id,
            ]);
            print($user->username . '<br>');
            // add bank account
            if ($a->member_bank_name) {
                $bank = Bank::where('name', 'like', '%' . $a->member_bank_name . '%')->first();
                if (!$bank) {
                    $bank = Bank::create([
                        'name' => strtoupper($a->member_bank_name),
                    ]);
                }
                $user->update([
                    'bank_id' => $bank->id,
                    'bank_account' => $a->member_bank_account_number,
                    'bank_as' => $a->member_bank_account_name,
                ]);
            }
            // set stockist
            if ($a->stockist) {
                $user->update([
                    'is_stockist' => true,
                ]);
            }
            // add address
            if ($a->member_province_id) {
                $user->addresses()->create([
                    'is_active' => true,
                    'name' => 'Rumah',
                    'email' => $a->member_email,
                    'address' => $a->member_address,
                    'phone' => $a->member_phone,
                    'province_id' => $a->member_province_id,
                    'city_id' => $a->member_city_id,
                    'subdistrict_id' => $a->member_subdistrict_id,
                ]);
            }
        }
        User::whereNull('id')->update([
            'image' => null,
        ]);
        dd('success');
    }

    public function db2test()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        $members = Member::orderBy('member_datetime', 'asc')->get();
        dd($members->take(50));
        foreach ($members as $key => $a) {
            print($a->member_username . '|' . $a->member_password . '<br>');
        }
        dd('success');
    }

    public function planA()
    {
        $response = Http::asForm()->post(env('CR_URL') . '/api/member/login', [
            'username' => Auth::user()->username,
            'password' => Auth::user()->member->member_password ?? Auth::user()->password,
            // 'password' => '$2y$10$bb6LJPT8x9jsXRrKljf7heOgv9aBV6V0VbjE/klHmoX5fdpzUylu2', // 1234
        ]);
        if ($response->successful()) {
            $sso_url = $response->json()['data']['sso_url'];
            if (request()->redirect) {
                return redirect($sso_url . '?redirect=' . request()->redirect);
            }
            return redirect($sso_url);
        }
        Session::flash('fail', 'Terjadi kesalahan saat masuk ke Plan A');
        return back();
    }

    public function profitSharing13()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        $month = request()->month ?? date('Y-m');
        $date = DateTime::createFromFormat('Y-m', $month);
        if (Auth::user()->type == 'member') {
            if (!Auth::user()->monthlyRoyaltyQualified($month)) {
                return [
                    'poin' => 0,
                    'qualified' => 0,
                    'amount' => 0,
                ];
            }
        }
        if (Helper::isClosing($month)) {
            $bonuses = Bonus::where('type', 'Bonus Royalti Profit Sharing 13%')->whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->get();
            $qualified = $bonuses->count();
            $amount = $qualified ? $bonuses->first()->amount : 0;
            return [
                'poin' => round($amount * $qualified / 130),
                // 130 = 1000pv * 13%
                'qualified' => $qualified,
                'amount' => $amount,
            ];
        }
        if (env('APP_ENV') == 'production') {
            return [
                'poin' => 0,
                'qualified' => 0,
                'amount' => 0,
            ];
        }
        $poin = Helper::transactionPoin($date);
        // get all user where has transaction
        $users_count = User::whereIn('id', Helper::transactionUsers($date))->get()->filter(function ($a) use ($month) {
            return $a->monthlyRoyaltyQualified($month) == true;
        })->count();
        return [
            'poin' => $poin,
            'qualified' => $users_count,
            'amount' => $users_count ? round($poin * 130 / $users_count) : 0, // 130 = 1000pv * 13%
        ];
    }

    public function qualified()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        $month = request()->month ?? date('Y-m');
        $date = DateTime::createFromFormat('Y-m', $month);

        $userCollections = collect();
        if (Helper::isClosing($month)) {
            $users = User::whereHas('bonuses', function ($q) use ($date) {
                $q->where('type', 'Bonus Unilevel RO')->whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'));
            })->select('id', 'username', 'name')
                ->get()
                ->makeHidden(['rank', 'image_path', 'premium_user_pin'])
                ->map(function ($user) use ($month, $userCollections) {
                    $user['poin'] = $user->monthlyPoin($month);
                    $user['bonus'] = (int) $user->monthlyBonuses($month)->where('type', 'Bonus Unilevel RO')->sum('amount');
                    $userCollections->push($user);
                    return $user;
                });
        } else {
            $users = User::whereIn('id', Helper::transactionUsers($date))
                ->select('id', 'username', 'name')
                ->get()
                ->makeHidden(['rank', 'image_path', 'premium_user_pin'])
                ->filter(function ($a) use ($month) {
                    return $a->monthlyQualified($month) == true;
                })
                ->map(function ($user) use ($month, $userCollections) {
                    $user['poin'] = $user->monthlyPoin($month);
                    $user['bonus'] = 0;
                    $userCollections->push($user);
                    return $user;
                });
        }
        return [
            'data' => $userCollections,
        ];
    }

    public function royalty()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');

        $month = request()->month ?? date('Y-m');
        $date = DateTime::createFromFormat('Y-m', $month);

        $userCollections = collect();
        if (Helper::isClosing($month)) {
            $users = User::whereHas('bonuses', function ($q) use ($date) {
                $q->where('type', 'Bonus Royalti Profit Sharing 13%')->whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'));
            })->select('id', 'username', 'name')
                ->get()
                ->makeHidden(['rank', 'image_path', 'premium_user_pin'])
                ->map(function ($user) use ($month, $userCollections) {
                    $user['poin'] = $user->monthlyPoin($month);
                    $user['bonus'] = (int) $user->monthlyBonuses($month)->where('type', 'Bonus Royalti Profit Sharing 13%')->sum('amount');
                    $userCollections->push($user);
                    return $user;
                });
        } else {
            $users = User::whereIn('id', Helper::transactionUsers($date))
                ->select('id', 'username', 'name')
                ->get()
                ->makeHidden(['rank', 'image_path', 'premium_user_pin'])
                ->filter(function ($a) use ($month) {
                    return $a->monthlyRoyaltyQualified($month) == true;
                })
                ->map(function ($user) use ($month, $userCollections) {
                    $user['poin'] = $user->monthlyPoin($month);
                    $user['bonus'] = 0;
                    $userCollections->push($user);
                    return $user;
                });
        }
        return [
            'data' => $userCollections,
        ];
    }
}
