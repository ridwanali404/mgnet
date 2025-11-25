<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPin extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function pin()
    {
        return $this->belongsTo(Pin::class);
    }
}
