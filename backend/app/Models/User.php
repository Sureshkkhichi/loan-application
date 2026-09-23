<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'fcm_token',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function loanApplications()
    {
        return $this->hasMany(LoanApplication::class, 'customer_id');
    }

    public function assignedApplications()
    {
        return $this->hasMany(LoanApplication::class, 'assigned_sales_id');
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'sales_executive']);
    }

    public function isManagerOrAdmin(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }
}
