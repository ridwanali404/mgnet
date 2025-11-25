<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BigProduct extends Model
{
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function childProduct()
    {
        return $this->belongsTo(Product::class, 'child_product_id');
    }
}
