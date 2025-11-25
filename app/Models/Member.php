<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'member';
    protected $primaryKey = 'member_id';

    public function user()
    {
        return $this->setConnection('mysql')->hasOne(User::class, 'member_id', 'member_id');
    }

    public function memberNetworks()
    {
        return $this->hasMany(MemberNetwork::class, 'member_network_member_id', 'member_id')->where('member_network_order', 1);
    }

    public function stockist()
    {
        return $this->hasOne(Stockist::class, 'stockist_member_id', 'member_id');
    }

    public function memberPhases()
    {
        return $this->hasMany(MemberPhase::class, 'member_phase_member_id', 'member_id');
    }
}
