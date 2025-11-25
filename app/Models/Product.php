<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
    ];

    protected $appends = ['dash_name', 'image_path', 'price_used'];

    public function getDashNameAttribute()
    {
        return str_replace(' ', '-', $this->name);
    }

    public function getImagePathAttribute()
    {
        if (!$this->images)
            return 'img/default-product-image.jpg';
        else
            return 'storage/' . $this->images[0];
    }

    public function getPriceUsedAttribute()
    {
        // if (Auth::user() && Auth::user()->member && Auth::user()->member?->member_phase_name != 'User Free') {
        // if (Auth::user() && !in_array(Auth::user()->phase, ['User Free', null])) {
        if (Auth::user() && Auth::user()->premiumUserPin) {
            if (session('mode') == 'stockist') {
                if (Auth::user()->is_master_stockist) {
                    return $this->price_master;
                }
                if (Auth::user()->is_stockist) {
                    return $this->price_stockist;
                }
            }
            return $this->price_member;
        }
        return $this->price;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function priceUsedByUser(User $user)
    {
        // if ($user && $user->member && $user->member->phase != 'User Free') {
        if ($user && $user->premiumUserPin) {
            if ($user->is_master_stockist) {
                return $this->price_master;
            }
            if ($user->is_stockist) {
                return $this->price_stockist;
            }
            return $this->price_member;
        }
        return $this->price;
    }

    public function bigProducts()
    {
        return $this->hasMany(BigProduct::class);
    }

}
