<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'country',
        'city',
        'address',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
