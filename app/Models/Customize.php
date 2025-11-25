<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customize extends Model
{
    protected $table = 'customizes';
    protected $fillable = [
        'title',
        'meta_description',
        'meta_keywords',
        'image'
    ];
    protected $appends = ['image_path'];
    public function getImagePathAttribute()
    {
        if(!$this->image) return 'img/favicon.png';
        else return $this->image;
    }
}
