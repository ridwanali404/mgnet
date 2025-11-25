<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    protected $table = 'contact_uses';
    protected $fillable = [
        'company',
        'address_line_1',
        'address_line_2',
        'phone',
        'text',
        'email',
        'instagram',
        'facebook',
        'youtube'
    ];
}
