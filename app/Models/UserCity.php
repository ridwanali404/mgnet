<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCity extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }
}
