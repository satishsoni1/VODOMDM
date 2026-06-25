<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    protected $fillable = [
        'type', 'action', 'status', 'summary',
        'request_data', 'response_data', 'error_message',
        'duration_ms', 'records_in', 'records_out',
        'parent_log_id', 'triggered_by', 'ip_address',
    ];

    protected $casts = [
        'request_data'  => 'array',
        'response_data' => 'array',
    ];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_log_id');
    }

    public function steps()
    {
        return $this->hasMany(self::class, 'parent_log_id');
    }

    public function isSuccess(): bool { return $this->status === 'success'; }
    public function isFailed(): bool  { return $this->status === 'failed'; }
    public function isRunning(): bool { return $this->status === 'running'; }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'success' => 'success',
            'failed'  => 'danger',
            'running' => 'warning',
            default   => 'secondary',
        };
    }

    /** Record a step under a parent run log */
    public static function step(int $parentId, string $action, string $summary, array $extra = []): self
    {
        return self::create(array_merge([
            'parent_log_id' => $parentId,
            'type'          => 'step',
            'action'        => $action,
            'status'        => 'success',
            'summary'       => $summary,
        ], $extra));
    }

    /** Mark a log entry complete with timing */
    public function finish(string $status = 'success', ?string $summary = null, array $extra = []): self
    {
        $started = $this->created_at ?? now();
        $this->update(array_merge([
            'status'      => $status,
            'duration_ms' => (int) ($started->diffInMilliseconds(now())),
        ], $summary ? ['summary' => $summary] : [], $extra));
        return $this;
    }
}
