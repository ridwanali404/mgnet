<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use File;
use Image;
use Storage;
use App\Models\Banner;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banners = Banner::orderBy('number')->get();
        return view('marketplace.admin.banner', compact('banners'));
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
        $imagePath = null;
        if($request->image) $imagePath = $this->uploadImage($request->image);
        else if($request->image_url) $imagePath =  $this->uploadImage($request->image_url);
        $is_saved = Banner::create(array(
            'number' => $request->number,
            'image' => $imagePath
        ));
        if($is_saved) Session::flash('success', 'Saved');
        else Session::flash('fail', 'Error while saving');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);
        if($request->image) {
            if($banner->image) File::delete(public_path($banner->image));
            $imagePath = $this->uploadImage($request->image);
        }
        else if($request->image_url) {
            if($banner->image) File::delete(public_path($banner->image));
            $imagePath = $this->uploadImage($request->image_url);
        }
        else $imagePath = $banner->image;
        $is_updated = $banner->update(array(
            'number' => $request->number,
            'image' => $imagePath
        ));
        if($is_updated) Session::flash('success', 'Updated');
        else Session::flash('fail', 'Error while updating');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $banner = Banner::find($id);
        if($banner->image) File::delete(public_path($banner->image));
        $is_deleted = $banner->delete();
        if($is_deleted) Session::flash('success', 'Deleted');
        else Session::flash('fail', 'Error while deleting');
        return back();
    }

    public function uploadImage($image) {
        $path = 'storage/upload/banner/';
        File::exists($path) or File::makeDirectory($path, 0777, true, true);
        if(!is_string($image)) {
            $imageName = date('Ymd').time().'.'.$image->getClientOriginalExtension();
            $imagePath = $path.$imageName;
            $img = Image::make($image->getRealPath());
        }
        else {
            // every url will be formatted to jpg
            $imageName = date('Ymd').time().'.jpg';
            $imagePath = $path.$imageName;
            $img = Image::make($image);
        }
        // resize the image to a width of 300 and constraint aspect ratio (auto height)
        $img->resize(1900, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        // resize the image to a height of 200 and constraint aspect ratio (auto width)
        $img->resize(null, 646, function ($constraint) {
            $constraint->aspectRatio();
        });
        // prevent possible upsizing
        $img->resize(null, 1200, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        // save
        $img->save($imagePath, 72);
        return $imagePath;
    }
}
