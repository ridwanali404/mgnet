<?php

namespace App\Http\Controllers;

use App\Models\Topup;
use App\Traits\Helper;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class TopupController extends Controller
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
        auth()->user()->topups()->create($request->all());
        session()->flash('success', 'Topup berhasil dibuat');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Topup  $topup
     * @return \Illuminate\Http\Response
     */
    public function show(Topup $topup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Topup  $topup
     * @return \Illuminate\Http\Response
     */
    public function edit(Topup $topup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Topup  $topup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Topup $topup)
    {
        Storage::delete($request->receipt);
        if ($request->hasFile('receipt')) {
            $image_name = 'receipt_' . date('YmdHis') . round(microtime(true) * 1000) . '.' . $request->receipt->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('receipt', $request->receipt, $image_name);
            $image = Image::make(storage_path('app/public/' . $path));
            $width = 512;
            $height = 512;
            if ($image->height() > 512 || $image->width() > 512) {
                $image->height() > $image->width() ? $width = null : $height = null;
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                })->save();
            } else {
                $image->save();
            }
            $topup->update([
                'receipt' => $path,
            ]);
        }
        session()->flash('success', 'Topup berhasil diubah');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Topup  $topup
     * @return \Illuminate\Http\Response
     */
    public function destroy(Topup $topup)
    {
        Storage::delete($topup->receipt);
        $topup->delete();
        session()->flash('success', 'Topup berhasil dibatalkan');
        return back();
    }

    public function confirm(Request $request, Topup $topup)
    {
        $topup->update([
            'confirm_at' => now(),
        ]);
        Helper::automaintain($topup->user, 'K', $topup->amount * 10, 'Topup automaintain.');
        session()->flash('success', 'Konfirmasi berhasil');
        return back();
    }
}