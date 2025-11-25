<?php

namespace App\Http\Controllers;

use App\Models\Topup;
use App\Traits\Helper;
use App\Models\Automaintain;
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
        } else {
            $topups = auth()->user()->topups;
        }
        return view('automaintain', compact('automaintains', 'topups'));
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
        if ($request->qty > floor(auth()->user()->cash_automaintain / 2000000)) {
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