<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use Session;
use Image;
use Storage;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles = Article::latest()->get();
        return view('article', compact('articles'));
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
        $r = $request->all();
        unset($r['image']);
        if ($request->hasFile('image')) {
            $image_name = 'article_' . date('YmdHis') . round(microtime(true) * 1000) . '.' . $request->image->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('article', $request->image, $image_name);
            $image = Image::make(storage_path('app/public/' . $path));
            $image->fit(900, 350, function($constraint){
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save();
            $r['image'] = $path;
        }
        $r['slug'] = Str::slug($r['title'], '-');
        Article::create($r);
        Session::flash('success', 'Artikel dibuat');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function edit(Article $article)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        $r = $request->all();
        unset($r['image']);
        if ($request->hasFile('image')) {
            $image_name = 'article_' . date('YmdHis') . round(microtime(true) * 1000) . '.' . $request->image->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('article', $request->image, $image_name);
            $image = Image::make(storage_path('app/public/' . $path));
            $image->fit(900, 350, function($constraint){
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save();
            $r['image'] = $path;
            Storage::disk('public')->delete($article->image);
        }
        $r['slug'] = Str::slug($r['title'], '-');
        $article->udpate($r);
        Session::flash('success', 'Artikel diubah');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        Storage::disk('public')->delete($article->image);
        $article->delete();
        Session::flash('success', 'Artikel dihapus');
        return back();
    }
}
