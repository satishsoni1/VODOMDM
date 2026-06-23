<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientProject extends Model
{
    protected $fillable = [
        'client_id', 'name', 'code', 'description', 'region',
        'start_date', 'end_date', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(DeviceAllocation::class);
    }
}
