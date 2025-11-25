<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'city';
    protected $primaryKey = 'city_id';
    protected $guarded = [];

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }
}
