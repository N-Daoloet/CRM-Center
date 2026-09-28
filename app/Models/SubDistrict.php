<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubDistrict extends Model
{
    protected $fillable = [
        'id',
        'zip_code',
        'name_th',
        'name_en',
        'district_id',
        'lat',
        'long',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
