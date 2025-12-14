<?php

namespace App\Http\Controllers;

use App\Models\Topup;
use App\Traits\Helper;
use App\Models\Automaintain;
use App\Models\User;
use Illuminate\Http\Request;

class AutomaintainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $automaintains = auth()->user()->automaintains()->orderBy('id', 'desc')->paginate(10);
        if (auth()->user()->type == 'admin') {
            $topups = Topup::latest()->get();
            
            // Members with automaintain balance (with last update date)
            $membersWithAutomaintain = User::where('type', 'member')
                ->where('cash_automaintain', '>', 0)
                ->with(['automaintains' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(1);
                }])
                ->orderBy('cash_automaintain', 'desc')
                ->get()
                ->map(function($user) {
                    $lastAutomaintain = $user->automaintains->first();
                    $user->last_automaintain_update = $lastAutomaintain ? $lastAutomaintain->created_at : null;
                    return $user;
                });
            
            // Members who have already claimed automaintain (type='D' and amount=1700000)
            $membersAlreadyAutomaintain = User::where('type', 'member')
                ->whereHas('automaintains', function($query) {
                    $query->where('type', 'D')
                        ->where('amount', 1700000);
                })
                ->with(['automaintains' => function($query) {
                    $query->where('type', 'D')
                        ->where('amount', 1700000)
                        ->orderBy('created_at', 'desc')
                        ->limit(1);
                }])
                ->get()
                ->map(function($user) {
                    $lastClaim = $user->automaintains->first();
                    $user->last_automaintain_date = $lastClaim ? $lastClaim->created_at : null;
                    return $user;
                })
                ->filter(function($user) {
                    return $user->last_automaintain_date !== null;
                })
                ->sortByDesc('last_automaintain_date')
                ->values();
        } else {
            $topups = auth()->user()->topups;
            $membersWithAutomaintain = collect();
            $membersAlreadyAutomaintain = collect();
        }
        return view('automaintain', compact('automaintains', 'topups', 'membersWithAutomaintain', 'membersAlreadyAutomaintain'));
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Automaintain  $automaintain
     * @return \Illuminate\Http\Response
     */
    public function show(Automaintain $automaintain)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Automaintain  $automaintain
     * @return \Illuminate\Http\Response
     */
    public function edit(Automaintain $automaintain)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Automaintain  $automaintain
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Automaintain $automaintain)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Automaintain  $automaintain
     * @return \Illuminate\Http\Response
     */
    public function destroy(Automaintain $automaintain)
    {
        //
    }

    public function claim(Request $request)
    {
        // Automaintain limit adalah 1,7 juta (bukan 2 juta)
        if ($request->qty > floor(auth()->user()->cash_automaintain / 1700000)) {
            session()->flash('fail', 'Saldo automaintain belum cukup');
            return back();
        }
        for ($i = 0; $i < $request->qty; $i++) {
            Helper::ro(auth()->user());
        }
        session()->flash('success', 'Klaim automaintain berhasil');
        return back();
    }
}