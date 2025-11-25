<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'image'
    ];
    protected $appends = ['image_path, dash_name'];
    public function products()
    {
        return $this->hasMany('App\Product');
    }
    public function getImagePathAttribute()
    {
        if(!$this->image) return 'img/default-product-image.jpg';
        else return $this->image;
    }
    public function getDashNameAttribute()
    {
        return str_replace(' ', '-', $this->name);
    }
}
