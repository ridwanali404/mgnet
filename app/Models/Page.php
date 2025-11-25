<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'name',
        'content'
    ];
    protected $appends = ['dash_name'];
    public function getDashNameAttribute()
    {
        return str_replace(' ', '-', $this->name);
    }
}
