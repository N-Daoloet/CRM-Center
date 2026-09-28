<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'campaign_type',
        'description',
        'start_date',
        'end_date',
        'status',
        'pricing_type',
        'pricing_value',
        'discounted_total',
    ];

    protected $casts = [
        'pricing_value' => 'decimal:2',
        'discounted_total' => 'decimal:2',
    ];

    public function calls()
    {
        return $this->hasMany(Call::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('special_price')
            ->withTimestamps();
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class)
            ->withTimestamps();
    }
}
