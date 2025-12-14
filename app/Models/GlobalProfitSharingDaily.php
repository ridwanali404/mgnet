<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalProfitSharingDaily extends Model
{
    protected $table = 'global_profit_sharing_daily';
    
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
