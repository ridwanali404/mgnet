<?php

namespace App\Http\Controllers;

use App\Models\KeyValue;
use Illuminate\Http\Request;
use Session;

class KeyValueController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\KeyValue  $keyValue
     * @return \Illuminate\Http\Response
     */
    public function show(KeyValue $keyValue)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\KeyValue  $keyValue
     * @return \Illuminate\Http\Response
     */
    public function edit(KeyValue $keyValue)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\KeyValue  $keyValue
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, KeyValue $keyValue)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\KeyValue  $keyValue
     * @return \Illuminate\Http\Response
     */
    public function destroy(KeyValue $keyValue)
    {
        //
    }

    public function keyValue(Request $request)
    {
        KeyValue::updateOrCreate(['key' => 'banner_title'], [
            'value' => $request->banner_title,
        ]);
        KeyValue::updateOrCreate(['key' => 'banner_subtitle'], [
            'value' => $request->banner_subtitle,
        ]);
        KeyValue::where('key', 'testimony')->first()->update([
            'value' => $request->testimony,
        ]);
        KeyValue::where('key', 'testimony_text')->first()->update([
            'value' => $request->testimony_text,
        ]);
        KeyValue::where('key', 'testimony_footer')->first()->update([
            'value' => $request->testimony_footer,
        ]);
        Session::flash('success', 'Saved');
        return back();
    }
}
