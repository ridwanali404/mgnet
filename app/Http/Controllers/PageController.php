<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\Models\Page;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pages = Page::orderBy('created_at')->get();
        return view('marketplace.admin.page', compact('pages'));
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
        $is_saved = Page::create($request->all());
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
        $is_updated = Page::find($id)->update($request->all());
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
        $is_deleted = Page::find($id)->delete();
        if($is_deleted) Session::flash('success', 'Deleted');
        else Session::flash('fail', 'Error while deleting');
        return back();
    }

    public function publicPageDetail($title)
    {
        $page = Page::where('name', str_replace('-', ' ', $title))->first();
        if($page) return view('marketplace.page_detail', compact('page'));
        else return back();
    }
}
