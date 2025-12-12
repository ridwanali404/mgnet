<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTripReward extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tripReward()
    {
        return $this->belongsTo(TripReward::class);
    }
}
