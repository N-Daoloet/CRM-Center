<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAssignment extends Model
{
    protected $fillable = [
        'customer_id',
        'salesman_id',
        'assigned_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
