<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    protected $table = 'about_uses';
    protected $fillable = [
        'title',
        'sub_title',
        'text',
        'image',
        'video'
    ];
    protected $appends = ['image_path'];
    public function getImagePathAttribute()
    {
        if(!$this->image) return 'img/about_us/dashboard.png';
        else return $this->image;
    }
}
