<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'province';
    protected $primaryKey = 'province_id';
    protected $guarded = [];

    public function cities()
    {
        return $this->hasMany(City::class, 'province_id', 'province_id');
    }
}
