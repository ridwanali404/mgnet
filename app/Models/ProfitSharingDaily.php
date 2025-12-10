<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitSharingDaily extends Model
{
    protected $table = 'profit_sharing_daily';
    
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
