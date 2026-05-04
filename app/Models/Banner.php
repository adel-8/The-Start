<?php

// app/Models/Banner.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Banner extends Model
{
    protected $fillable = [
        'title', 'image_url', 'link', 'position', 'status',
        'starts_at', 'ends_at', 'device_type', 'clicks'
    ];

    protected $casts = [
        'status' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    // Scope for active banners (by date, status, device)
    public function scopeActive($query, $device = null)
    {
        $now = Carbon::now();
        return $query->where('status', true)
            ->where(function($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->when($device && $device !== 'all', function($q) use ($device) {
                $q->where('device_type', $device)->orWhere('device_type', 'all');
            })
            ->orderBy('position');
    }

    // Increment click count
    public function incrementClicks()
    {
        $this->increment('clicks');
    }
}