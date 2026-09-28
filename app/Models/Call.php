<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $fillable = [
        'customer_id',
        'salesman_id',
        'campaign_id',
        'call_time',
        'result',
        'next_step',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
