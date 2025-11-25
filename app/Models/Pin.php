<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pin extends Model
{
    protected $guarded = [];
    protected $appends = ['name_short'];

    public function getNameShortAttribute()
    {
        if (!str_contains($this->name, 'Upgrade') && !str_contains($this->name, 'Generasi') && !str_contains($this->name, 'BSM')) {
            return $this->name;
        }
        $pin = explode(' ', ucwords(strtolower($this->name)));
        if (in_array(end($pin), ['Up', 'Automaintain'])) {
            return $pin[count($pin) - 2];
        }
        return end($pin);
    }
}