<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyProfit extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pp()
    {
        return $this->belongsTo(User::class, 'pp_id');
    }

    public function pr()
    {
        return $this->belongsTo(User::class, 'pr_id');
    }
}