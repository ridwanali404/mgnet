<?php

namespace App\Http\Controllers;

use App\Models\UserPin;
use App\Models\Pin;
use App\Models\User;
use Illuminate\Http\Request;
use Session;
use Auth;
use App\Models\PinHistory;
use App\Traits\Helper;

class UserPinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pins = \App\Models\Pin::where('type', '!=', 'free')->whereNotIn('name', ['CR Reseller', 'Silver', 'Gold', 'Platinum'])->orderBy('type')->orderBy('price')->get();
        if (Auth::user()->type == 'admin') {
            $userPins = UserPin::whereNull('id')->get();
            $buy_pin_histories = PinHistory::latest()->whereNull('to_id')->get();
            $transfer_pin_histories = PinHistory::latest()->whereNotNull('to_id')->get();
        } else {
            $userPins = Auth::user()->boughtUserPins()->whereNotNull('buyer_id')->latest()->get();
            $buy_pin_histories = Auth::user()->buyPinHistories()->latest()->get();
            $transfer_pin_histories = Auth::user()->transferPinHistories()->latest()->get();
        }
        return view('pin-history', compact('pins', 'buy_pin_histories', 'transfer_pin_histories', 'userPins'));
        // $userPins = UserPin::whereHas('pin', function ($q) {
        //     $q->where('type', 'premium');
        // })->latest()->get();
        // $pins = \App\Models\Pin::where('type', 'premium')->orderBy('price')->get();
        // return view('userPin', compact('userPins', 'pins'));
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
        // create pin
        $r = $request->all();
        $pin = \App\Models\Pin::find($r['pin_id']);
        $r['code'] = strtoupper(str_random(6));
        $r['name'] = $pin->name;
        $r['price'] = $pin->price;
        $r['level'] = $pin->level;
        $r['is_used'] = true;
        $userPin = UserPin::create($r);
        Helper::pinHistory($userPin);
        // Bonus Unilevel Mingguan
        $sponsor = $userPin->user->sponsor;
        for ($i = 1; $i <= 10; $i++) {
            $percent = \App\Models\KeyValue::where('key', 'weekly_unilevel_' . $i)->value('value');
            if (!$sponsor)
                break;
            if ($sponsor->userPin->price || $i == 1) {
                $sponsor->bonuses()->create([
                    'type' => 'Bonus Unilevel Mingguan',
                    'amount' => round($userPin->price * ($sponsor->userPin->price ? $percent : 10) / 100),
                    'description' => 'Bonus Unilevel Mingguan dari beli pin pendaftaran ' . $userPin->user->username . '.',
                ]);
            }
            $sponsor = $sponsor->sponsor;
        }
        Session::flash('success', 'Beli pin pendaftaran berhasil');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserPin  $userPin
     * @return \Illuminate\Http\Response
     */
    public function show(UserPin $userPin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserPin  $userPin
     * @return \Illuminate\Http\Response
     */
    public function edit(UserPin $userPin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserPin  $userPin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserPin $userPin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserPin  $userPin
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserPin $userPin)
    {
        //
    }

    public function generate(Request $request)
    {
        $pin = Pin::find($request->pin_id);
        if ($request->use) {
            if ($request->amount != 1) {
                Session::flash('fail', 'Isi jumlah dengan 1 apabila akan langsung menggunakan pin');
                return back();
            }
            $user = User::find($request->buyer_id);
            if (!$user->checkUsablePin($pin)) {
                Session::flash('fail', 'Pin tidak valid');
                return back();
            }
        }
        for ($i = 0; $i < $request->amount; $i++) {
            $userPin = UserPin::create([
                'buyer_id' => $request->buyer_id,
                'pin_id' => $request->pin_id,
                'code' => strtoupper(str_random(6)),
                'name' => $pin->name,
                'price' => $pin->price,
                'level' => $pin->level,
            ]);
        }
        PinHistory::create([
            'pin_id' => $request->pin_id,
            'user_id' => $request->buyer_id,
            'qty' => $request->amount,
        ]);
        if ($request->use) {
            $userPin->update([
                'user_id' => $request->buyer_id,
                'is_used' => true,
            ]);
            Helper::upgrade($userPin);
        }
        Session::flash('success', 'PIN Pendaftaran berhasil dibuat');
        return back();
    }

    public function transfer(Request $request)
    {
        $pins = Auth::user()->usableUserPins()->where('pin_id', $request->pin_id)->limit($request->amount)->get();
        if ($pins->count() != $request->amount) {
            Session::flash('fail', 'PIN gagal ditransfer, jumlah pin yang akan ditrasfer melebihi jumlah usable pin anda');
            return back();
        }
        foreach ($pins as $pin) {
            $pin->update(['buyer_id' => $request->buyer_id]);
        }
        PinHistory::create(
            array(
                'pin_id' => $request->pin_id,
                'user_id' => Auth::id(),
                'to_id' => $request->buyer_id,
                'qty' => $request->amount,
            )
        );
        Session::flash('success', 'PIN berhasil ditransfer');
        return back();
    }

    public function memberBasic(Request $request)
    {
        if (!$request->member_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_id is required',
            ], 400);
        }
        $user = User::where('member_id', $request->member_id)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'member_id not found',
            ], 500);
        }

        if (!in_array($user->userPin->name, ['Free Member', 'CR Reseller'])) {
            return $user->userPin;
        }

        $pin = Pin::where('name', 'Basic')->first();
        $userPin = UserPin::create([
            'buyer_id' => $user->id,
            'pin_id' => $pin->id,
            'code' => strtoupper(str_random(6)),
            'name' => $pin->name,
            'price' => $pin->price,
            'level' => $pin->level,
            'user_id' => $user->id,
        ]);
        PinHistory::create([
            'pin_id' => $pin->id,
            'user_id' => $user->id,
            'qty' => 1,
        ]);

        return $userPin;
    }
}