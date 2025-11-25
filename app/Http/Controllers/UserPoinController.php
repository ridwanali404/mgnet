<?php

namespace App\Http\Controllers;

use App\Models\UserPoin;
use Illuminate\Http\Request;
use Session;
use Carbon\Carbon;

class UserPoinController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $date = Carbon::createFromFormat('Y-m', $request->date);
        $userPoin = UserPoin::whereYear('date', $date->format('Y'))->whereMonth('date', $date->format('m'))->where('user_id', $request->user_id)->first();
        if ($userPoin) {
            $userPoin->update([
                'poin' => $request->poin,
            ]);
        } else {
            $request->request->add([
                'date' => $date->format('Y-m-d'),
            ]);
            UserPoin::create($request->all());
        }
        Session::flash('success', 'Poin berhasil dibuat');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserPoin  $userPoin
     * @return \Illuminate\Http\Response
     */
    public function show(UserPoin $userPoin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserPoin  $userPoin
     * @return \Illuminate\Http\Response
     */
    public function edit(UserPoin $userPoin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserPoin  $userPoin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserPoin $userPoin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserPoin  $userPoin
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserPoin $userPoin)
    {
        $userPoin->delete();
        Session::flash('success', 'Poin berhasil dihapus');
        return back();
    }
}
