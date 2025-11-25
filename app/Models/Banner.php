<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'number',
        'image'
    ];
    protected $appends = ['image_path'];
    public function getImagePathAttribute()
    {
        if(!$this->image) return 'img/default-product-image.jpg';
        else return $this->image;
    }
}
