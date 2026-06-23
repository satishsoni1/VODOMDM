<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceProvider extends Model
{
    protected $fillable = [
        'name', 'code', 'contact_person', 'phone', 'email', 'address', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function policies(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class);
    }
}
