<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Session;
use Image;
use Storage;
use Illuminate\Support\Str;
use Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::latest()->get();
        return view('product', compact('products'));
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
        unset($r['images']);
        // insert image to storage with 512x512
        if ($request->hasFile('images')) {
            $images = [];
            foreach($request->file('images') as $key => $file) {
                // save images
                $image_name = 'product_' . date('YmdHis') . round(microtime(true) * 1000) . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('product', $file, $image_name);
                $image = Image::make(storage_path('app/public/' . $path));
                $image->fit(512, 512, function($constraint){
                    $constraint->aspectRatio();
                })->save();
                array_push($images, $path);
            }
            $r['images'] = $images;
        }
        Product::create($r);
        Session::flash('success', 'Produk dibuat');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $bigProducts = $product->bigProducts()->latest()->get();
        return view('product_detail', compact('product', 'bigProducts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $r = $request->all();
        unset($r['images']);
        if (!$request->is_ro) {
            $r['is_ro'] = false;
        }
        if (!$request->is_weekly) {
            $r['is_weekly'] = false;
        }
        if (!$request->is_hidden) {
            $r['is_hidden'] = false;
        }
        if (!$request->is_big) {
            $r['is_big'] = false;
        }
        // insert image to storage with 512x512
        if ($request->hasFile('images')) {
            $images = $product->images ?? [];
            foreach($request->file('images') as $key => $file) {
                // save images
                $image_name = 'product_' . date('YmdHis') . round(microtime(true) * 1000) . '.' . $file->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('product', $file, $image_name);
                $image = Image::make(storage_path('app/public/' . $path));
                $width = $image->width();
                $height= $image->height();
                $size = ($width > $height) ? $width : $height;
                $size = ($size > 512) ? 512 : $size;
                if ($width > $height) {
                    $image->resize($size, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                } else {
                    $image->resize(null, $size, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }
                $image->resizeCanvas($size, $size, 'center', false, '#ffffff');
                $image->save();
                // $image->fit($size, $size, function($constraint){
                //     $constraint->aspectRatio();
                // })->save();
                array_push($images, $path);
            }
            $r['images'] = $images;
        }
        $product->update($r);
        Session::flash('success', 'Produk diubah');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        if ($product->images) {
            foreach ($product->images as $a) {
                Storage::disk('public')->delete($a);
            }
        }
        $product->delete();
        Session::flash('success', 'Berhasil dihapusß');
        return back();
    }

    public function imageMain(Request $request, Product $product, $key)
    {
        $images = $product->images;
        $temp = $images[0];
        $images[0] = $images[$key];
        $images[$key] = $temp;
        $product->update([
            'images' => $images,
        ]);
        Session::flash('success', 'Berhasil dijadikan gambar utama');
        return back();
    }

    public function imageDelete(Request $request, Product $product, $key)
    {
        $images = $product->images;
        Storage::disk('public')->delete($images[$key]);
        array_splice($images, $key, 1);
        $product->update([
            'images' => $images,
        ]);
        Session::flash('success', 'Berhasil dijadikan gambar utama');
        return back();
    }

    public function publicProduct()
    {
        $query = null;
        $category = null;
        $categories = \App\Models\Category::orderBy('name')->get();
        $products = Product::where('is_hidden', false)->orderBy('created_at');
        if(null !== request()->get('query')) {
            $query = request()->get('query');
            $products = $products->where('name', 'like', '%'.request()->get('query').'%');
        }
        if(null !== request()->get('category')) {
            $category = str_replace('-', ' ', request()->get('category'));
            $products = $products->whereHas('category', function ($q) use($category){
                $q->where('name', $category);
            });
        }
        if (Auth::guest() || session('mode') == 'stockist') {
            $products = $products->where('is_big', false);
        }
        $products = $products->get();
        return view('shop.product', compact('categories', 'products', 'query', 'category'));
    }

    public function publicProductDetail($name)
    {
        $product = Product::where('name', str_replace('-', ' ', $name))->first();
        if($product) return view('shop.product_detail', compact('product'));
        else return back();
    }

    public function official()
    {
        return Product::select('id', 'name as text')->where('is_ro', true)->where('is_big', false)->where('name', 'like', request()->get('search') . '%')->paginate(10);
    }

    public function get()
    {
        return Product::select('id', 'name')->where('is_ro', true)->latest()->get()->makeHidden(['dash_name', 'image_path', 'price_used']);
    }

    public function storeBig(Request $request)
    {
        $r = $request->all();
        // duplicate price
        Product::create($r);
        Session::flash('success', 'Produk dibuat');
        return back();
    }

    public function officialBig()
    {
        return Product::select('id', 'name as text', 'is_big', 'month')->where('is_ro', true)->where('name', 'like', request()->get('search') . '%')->paginate(10);
    }
}
