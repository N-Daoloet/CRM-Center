<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_code',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'position',
        'province_id',
        'district_id',
        'sub_district_id',
        'address',
        'zipcode',
        'team_sales_target',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'team_sales_target' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (empty($user->employee_code)) {
                $user->employee_code = self::generateNextEmployeeCode();
            }
        });
    }

    public static function generateNextEmployeeCode(): string
    {
        $latestCode = self::query()
            ->whereNotNull('employee_code')
            ->where('employee_code', 'like', 'EMP%')
            ->orderByDesc('employee_code')
            ->value('employee_code');

        if (! $latestCode) {
            return 'EMP0001';
        }

        $runningNumber = (int) substr($latestCode, 3);

        return 'EMP' . str_pad((string) ($runningNumber + 1), 4, '0', STR_PAD_LEFT);
    }

    public function setupTeamsAsSupervisor()
    {
        return $this->hasMany(SetupTeam::class, 'supervisor_user_id');
    }

    public function setupTeamsAsMember()
    {
        return $this->hasMany(SetupTeam::class, 'member_user_id');
    }

    public function customerAssignments()
    {
        return $this->hasMany(CustomerAssignment::class, 'salesman_id');
    }
}
