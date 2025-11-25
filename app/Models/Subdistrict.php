<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subdistrict extends Model
{
    protected $table = 'subdistrict';
    protected $primaryKey = 'subdistrict_id';
    protected $guarded = [];

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'city_id');
    }
}
