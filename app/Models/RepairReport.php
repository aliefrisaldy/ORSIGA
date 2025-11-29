<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepairReport extends Model
{
    protected $table = 'repair_reports';
    protected $primaryKey = 'id_repair_reports';
    // public $incrementing = true; // default, bisa dihapus
    // protected $keyType = 'int'; // default, bisa dihapus

    protected $fillable = [
        'ticket_number',
        'technician_name',
        'latitude',
        'longitude',
        'documentation',
        'work_details',
        'repair_type',
        'cable_type',
        'disruption_cause',
    ];

    protected $casts = [
        'documentation' => 'array', 
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // Relasi: Report ini punya banyak related reports
    public function relatedReports()
    {
        return $this->belongsToMany(
            RepairReport::class,
            'report_relations',              // pivot table
            'repair_report_id',              // foreign key untuk report ini
            'related_repair_report_id'       // foreign key untuk related report
        );
    }

    // Relasi: Report ini adalah related report dari banyak report lain
    public function reportsRelatedTo()
    {
        return $this->belongsToMany(
            RepairReport::class,
            'report_relations',
            'related_repair_report_id',      // foreign key untuk related report
            'repair_report_id'               // foreign key untuk report utama
        );
    }

    
}