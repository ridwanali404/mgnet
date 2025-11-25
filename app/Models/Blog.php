<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'image',
        'content'
    ];
    protected $appends = ['image_path', 'dash_title'];
    public function getImagePathAttribute()
    {
        if(!$this->image) return 'img/default-product-image.jpg';
        else return $this->image;
    }
    public function getDashTitleAttribute()
    {
        return str_replace(' ', '-', $this->title);
    }
}
