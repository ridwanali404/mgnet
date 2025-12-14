<?php

namespace App\Http\Controllers;

use App\Models\Topup;
use App\Traits\Helper;
use App\Models\Automaintain;
use App\Models\User;
use App\Models\WithdrawAutomaintain;
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
        
        // Get withdraw history (for admin, show all; for member, show only their own)
        $withdrawHistory = auth()->user()->type == 'admin' 
            ? WithdrawAutomaintain::with('user')->latest()->get()
            : collect();
        
        return view('automaintain', compact('automaintains', 'topups', 'membersWithAutomaintain', 'membersAlreadyAutomaintain', 'withdrawHistory'));
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

    public function withdraw(Request $request, User $user)
    {
        if (auth()->user()->type != 'admin') {
            session()->flash('fail', 'Akses ditolak');
            return back();
        }

        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        if ($user->cash_automaintain < $request->amount) {
            session()->flash('fail', 'Saldo automaintain tidak cukup');
            return back();
        }

        // Create withdraw record with pending status
        $withdraw = WithdrawAutomaintain::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'status' => 'pending'
        ]);

        // Decrease automaintain balance after submit
        $user->decrement('cash_automaintain', $request->amount);

        // Create automaintain record
        $user->automaintains()->create([
            'type' => 'D',
            'amount' => $request->amount,
            'current' => $user->cash_automaintain,
            'description' => 'Withdraw saldo automaintain',
        ]);

        // Update withdraw status to completed
        $withdraw->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        session()->flash('success', 'Withdraw berhasil diproses');
        return back();
    }

    public function cancelWithdraw(WithdrawAutomaintain $withdrawAutomaintain)
    {
        if (auth()->user()->type != 'admin') {
            session()->flash('fail', 'Akses ditolak');
            return back();
        }

        // Allow cancel for both pending and completed status
        if ($withdrawAutomaintain->status == 'cancelled') {
            session()->flash('fail', 'Withdraw sudah dibatalkan');
            return back();
        }

        // Restore automaintain balance (only if status is completed, pending doesn't have balance deducted yet)
        $user = $withdrawAutomaintain->user;
        if ($user && $withdrawAutomaintain->status == 'completed') {
            $user->increment('cash_automaintain', $withdrawAutomaintain->amount);
            
            // Create automaintain record for cancellation
            $user->automaintains()->create([
                'type' => 'K',
                'amount' => $withdrawAutomaintain->amount,
                'current' => $user->cash_automaintain,
                'description' => 'Pembatalan withdraw saldo automaintain',
            ]);
        }

        // Update withdraw status to cancelled
        $withdrawAutomaintain->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);

        session()->flash('success', 'Withdraw berhasil dibatalkan');
        return back();
    }
}