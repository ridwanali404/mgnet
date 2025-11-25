<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficialTransaction extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockist()
    {
        return $this->belongsTo(User::class, 'stockist_id');
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
