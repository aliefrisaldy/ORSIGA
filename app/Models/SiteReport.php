<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteReport extends Model
{
    protected $fillable = [
        'ticket_number',
        'site_id',
        'headline',
        'progress',
        'status',
        'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'Open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'Close');
    }

    // Helper methods
    public function isOpen(): bool
    {
        return $this->status === 'Open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'Close';
    }

    public function close(): void
    {
        $this->update([
            'status' => 'Close',
            'closed_at' => now(),
        ]);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => 'Open',
            'closed_at' => null,
        ]);
    }

    // Calculate Time To Recovery in hours (dengan desimal)
    public function getTimeToRecoveryAttribute(): ?float
    {
        if ($this->status === 'Close' && $this->created_at && $this->closed_at) {
            // Menggunakan diffInMinutes lalu dibagi 60 untuk hasil yang lebih akurat
            $minutes = $this->created_at->diffInMinutes($this->closed_at, true);
            return round($minutes / 60, 2);
        }
        
        return null;
    }

    // Get formatted Time To Recovery
    public function getFormattedTimeToRecoveryAttribute(): string
    {
        $ttr = $this->time_to_recovery;
        
        if ($ttr === null) {
            return '-';
        }

        // Jika kurang dari 1 jam, tampilkan dalam menit
        if ($ttr < 1) {
            $minutes = round($ttr * 60);
            return sprintf('%d minutes', $minutes);
        }

        $days = floor($ttr / 24);
        $remainingHours = $ttr % 24;

        if ($days > 0) {
            $hours = floor($remainingHours);
            $minutes = round(($remainingHours - $hours) * 60);
            
            if ($minutes > 0) {
                return sprintf('%dd %dh %dm', $days, $hours, $minutes);
            }
            return sprintf('%dd %dh', $days, $hours);
        }

        $hours = floor($ttr);
        $minutes = round(($ttr - $hours) * 60);
        
        if ($minutes > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        }
        
        return sprintf('%dh', $hours);
    }
    
    // Get Time To Recovery in different units
    public function getTimeToRecoveryInMinutesAttribute(): ?int
    {
        if ($this->status === 'Close' && $this->created_at && $this->closed_at) {
            return $this->created_at->diffInMinutes($this->closed_at, true);
        }
        
        return null;
    }
    
    public function getTimeToRecoveryInDaysAttribute(): ?float
    {
        $ttr = $this->time_to_recovery;
        return $ttr ? round($ttr / 24, 2) : null;
    }
}