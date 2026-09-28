<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    protected $table = 'salesmen';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'position',
        'supervisor_id',
        'sales_target',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function supervisor()
    {
        return $this->belongsTo(Salesman::class, 'supervisor_id');
    }

    public function teamMembers()
    {
        return $this->hasMany(Salesman::class, 'supervisor_id');
    }

    public function calls()
    {
        return $this->hasMany(Call::class);
    }
}
