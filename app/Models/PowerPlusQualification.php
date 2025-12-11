<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PowerPlusQualification extends Model
{
    protected $guarded = [];

    protected $casts = [
        'leg_omzets' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
