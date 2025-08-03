<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    protected $guarded = ['id'];

    protected $casts = [
        'show_contact' => 'boolean',
        'addresses' => 'array',
    ];
}
