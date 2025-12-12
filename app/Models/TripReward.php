<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripReward extends Model
{
    protected $guarded = [];

    public function userTripRewards()
    {
        return $this->hasMany(UserTripReward::class);
    }
}
