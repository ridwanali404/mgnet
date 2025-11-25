<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = [
        'name',
        'image'
    ];
    protected $appends = ['image_path'];
    public function getImagePathAttribute()
    {
        if(!$this->image) return 'img/default-product-image.jpg';
        else return $this->image;
    }
}
