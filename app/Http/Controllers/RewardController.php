<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use Illuminate\Http\Request;
use Session;

class RewardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rewards = Reward::orderBy('nominal')->get();
        return view('reward', compact('rewards'));
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
        Reward::create($request->all());
        Session::flash('success', 'Reward dibuat');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Reward  $reward
     * @return \Illuminate\Http\Response
     */
    public function show(Reward $reward)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reward  $reward
     * @return \Illuminate\Http\Response
     */
    public function edit(Reward $reward)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reward  $reward
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reward $reward)
    {
        if ($reward->is_platinum) {
            if (!isset($request->is_platinum)) {
                $request->request->add(['is_platinum' => false]);
            }
        }
        $reward->update($request->all());
        Session::flash('success', 'Reward diubah');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reward  $reward
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reward $reward)
    {
        $reward->delete();
        Session::flash('success', 'Reward dihapus');
        return back();
    }
}
