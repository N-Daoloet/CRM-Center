<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = [
        'id',
        'name_th',
        'name_en',
        'geography_id',
    ];

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
