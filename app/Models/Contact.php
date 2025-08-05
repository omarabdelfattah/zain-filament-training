<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'address_id',
        'email',
        'phone',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}