<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UmrohTripDaily extends Model
{
    protected $table = 'umroh_trip_daily';
    
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
