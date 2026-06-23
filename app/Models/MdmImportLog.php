<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MdmImportLog extends Model
{
    protected $fillable = [
        'imported_by', 'filename', 'total_rows', 'imported', 'updated',
        'skipped', 'auto_matched', 'status', 'notes',
    ];

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
