<?php

namespace App\Http\Controllers;

use App\Models\Award;
use Illuminate\Http\Request;
use Session;

class AwardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $awards = Award::orderBy('nominal')->get();
        return view('award', compact('awards'));
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
        Award::create($request->all());
        Session::flash('success', 'Reward dibuat');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\Response
     */
    public function show(Award $award)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\Response
     */
    public function edit(Award $award)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Award $award)
    {
        $award->update($request->all());
        Session::flash('success', 'Reward diubah');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\Response
     */
    public function destroy(Award $award)
    {
        $award->delete();
        Session::flash('success', 'Reward dihapus');
        return back();
    }
}