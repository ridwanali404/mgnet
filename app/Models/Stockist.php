<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stockist extends Model
{
    protected $connection = 'mysql2';
    protected $primaryKey = 'stockist_id';
    protected $table = 'stockist';

    public function member()
    {
        return $this->belongsTo(Member::class, 'stockist_member_id', 'member_id');
    }

    public function sponsor()
    {
        return $this->belongsTo(Member::class, 'stockist_sponsor_member_id', 'member_id');
    }
}
