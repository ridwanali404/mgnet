<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberPhase extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'member_phase';
    protected $primaryKey = 'member_phase_id';

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_phase_member_id', 'member_id');
    }

    public function memberPhaseDetails()
    {
        return $this->hasMany(MemberPhaseDetail::class, 'member_phase_detail_member_phase_id', 'member_phase_id');
    }
}
