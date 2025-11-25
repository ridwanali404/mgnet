<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PinHistory extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function to()
    {
        return $this->belongsTo(User::class, 'to_id');
    }

    public function pin()
    {
        return $this->belongsTo(Pin::class);
    }
}
