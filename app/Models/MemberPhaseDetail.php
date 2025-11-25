<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberPhaseDetail extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'member_phase_detail';

    public function memberPhase()
    {
        return $this->belongsTo(MemberPhase::class, 'member_phase_detail_member_phase_id', 'member_phase_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_phase_detail_member_id', 'member_id');
    }
}
