<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScanHelpVideo extends Model
{
    protected $fillable = ['title', 'video_url', 'description', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getEmbedUrlAttribute(): string
    {
        $url = trim($this->video_url);

        if (preg_match('~youtu\.be/([A-Za-z0-9_-]+)~', $url, $m) ||
            preg_match('~youtube\.com/watch\?v=([A-Za-z0-9_-]+)~', $url, $m) ||
            preg_match('~youtube\.com/shorts/([A-Za-z0-9_-]+)~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return $url;
    }
}
