<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'english_name',
        'email',
        'phone',
        'flag',
        'channel',
        'province_id',
        'district_id',
        'sub_district_id',
        'address',
        'zipcode',
        'status',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function calls()
    {
        return $this->hasMany(Call::class);
    }

    public function assignment()
    {
        return $this->hasOne(CustomerAssignment::class);
    }
}
