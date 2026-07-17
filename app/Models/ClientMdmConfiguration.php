<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientMdmConfiguration extends Model
{
    protected $fillable = ['client_id', 'configuration'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
