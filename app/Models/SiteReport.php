<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteReport extends Model
{
    protected $table = 'site_reports';

    protected $fillable = [
        'ticket_number',
        'site_id',
        'status',
        'headline',
        'progress',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'Open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'Close');
    }

    public function scopeByTicket($query, $ticketNumber)
    {
        return $query->where('ticket_number', $ticketNumber);
    }

    public function isOpen()
    {
        return $this->status === 'Open';
    }

    public function isClosed()
    {
        return $this->status === 'Close';
    }

    public function markAsOpen()
    {
        $this->update(['status' => 'Open']);
    }

    public function markAsClosed()
    {
        $this->update(['status' => 'Close']);
    }

    public function close()
    {
        return $this->markAsClosed();
    }

    public function reopen()
    {
        return $this->markAsOpen();
    }
}