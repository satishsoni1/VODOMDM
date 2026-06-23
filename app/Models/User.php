<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'client_id',
        'employee_code', 'phone', 'department', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function isClient(): bool
    {
        return $this->role?->slug === 'client';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role?->slug, ['super_admin', 'admin']);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'raised_by');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function recoveryCases(): HasMany
    {
        return $this->hasMany(RecoveryCase::class, 'assigned_to');
    }
}
