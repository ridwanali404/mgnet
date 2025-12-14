<?php

namespace App\Http\Controllers;

use App\Models\KeyValue;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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
        
        // Handle hapus gambar Coming Soon
        if ($request->has('delete_coming_soon_image') && $request->delete_coming_soon_image == '1') {
            $oldImage = KeyValue::where('key', 'coming_soon_image')->value('value');
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            KeyValue::where('key', 'coming_soon_image')->delete();
        }
        
        // Handle upload gambar Coming Soon
        if ($request->hasFile('coming_soon_image')) {
            $file = $request->file('coming_soon_image');
            
            // Hapus gambar lama jika ada (sebelum upload yang baru)
            $oldImage = KeyValue::where('key', 'coming_soon_image')->value('value');
            if ($oldImage && Storage::disk('public')->exists($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            
            // Pastikan folder coming_soon ada
            if (!Storage::disk('public')->exists('coming_soon')) {
                Storage::disk('public')->makeDirectory('coming_soon');
            }
            
            // Simpan gambar baru
            $imageName = 'coming_soon_' . date('YmdHis') . round(microtime(true) * 1000) . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('coming_soon', $file, $imageName);
            
            // Resize gambar jika perlu (opsional)
            $image = Image::make(storage_path('app/public/' . $path));
            $image->resize(800, null, function($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save();
            
            KeyValue::updateOrCreate(['key' => 'coming_soon_image'], [
                'value' => $path,
            ]);
        }
        
        Session::flash('success', 'Saved');
        return back();
    }
}
