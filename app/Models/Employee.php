<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'employee_code', 'name', 'email', 'mobile', 'password',
        'department', 'designation', 'role', 'status', 'permissions',
        'last_login_at', 'last_login_ip', 'failed_login_count',
        'locked_until', 'max_open_complaints',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password'       => 'hashed',
            'permissions'    => 'array',
            'last_login_at'  => 'datetime',
            'locked_until'   => 'datetime',
        ];
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_employee_id');
    }

    public function complaintLogs(): HasMany
    {
        return $this->hasMany(ComplaintLog::class, 'actor_id')
                    ->where('actor_type', 'employee');
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        return isset($this->permissions[$permission]) && $this->permissions[$permission] === true;
    }
}
