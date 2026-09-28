<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetupTeam extends Model
{
    protected $table = 'setup_teams';

    protected $fillable = [
        'supervisor_user_id',
        'member_user_id',
        'sales_target',
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_user_id');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'member_user_id');
    }
}
