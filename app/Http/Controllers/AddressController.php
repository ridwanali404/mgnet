<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Auth;
use Session;

class AddressController extends Controller
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
        if (request()->ajax()) {
            $r = $request->all();
            $r['is_active'] = true;
            unset($r['carts']);
            unset($r['address_id']);
            $address = Address::find($request->address_id);
            if ($address) {
                $address->update($r);
            } else {
                $address = Address::create($r);
            }
            return $address->id;
        }
        // check null province
        if (!$request->province_id) {
            Session::flash('fail', 'Province empty');
            return back();
        }
        // check null city
        if (!$request->city_id) {
            Session::flash('fail', 'City empty');
            return back();
        }
        // check null subdistrict
        if (!$request->subdistrict_id) {
            Session::flash('fail', 'Subdistrict empty');
            return back();
        }
        $r = $request->all();
        $r['is_active'] = true;
        Auth::user()->address()->create($r);
        Session::flash('success', 'Address saved');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function show(Address $address)
    {
        return Address::where('id', $address->id)->with(['province', 'city', 'subdistrict'])->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function edit(Address $address)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Address $address)
    {
        // check null city
        if (!$request->city_id) {
            Session::flash('fail', 'City empty');
            return back();
        }
        // check null subdistrict
        if (!$request->subdistrict_id) {
            Session::flash('fail', 'Subdistrict empty');
            return back();
        }
        $address->update($request->all());
        Session::flash('success', 'Address saved');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Address  $address
     * @return \Illuminate\Http\Response
     */
    public function destroy(Address $address)
    {
        //
    }
}
