<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberNetwork extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'member_network';

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_network_member_id', 'member_id');
    }

    public function sponsor()
    {
        return $this->belongsTo(Member::class, 'member_network_sponsor_member_id', 'member_id');
    }
}
