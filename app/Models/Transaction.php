<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function officialTransactions()
    {
        return $this->hasMany(OfficialTransaction::class);
    }
}
