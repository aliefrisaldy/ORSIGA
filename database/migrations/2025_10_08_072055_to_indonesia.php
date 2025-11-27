<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE report MODIFY COLUMN Penyelesaian_Gangguan VARCHAR(20) NULL");
        DB::statement("ALTER TABLE report MODIFY COLUMN Tipe_Kabel VARCHAR(20) NULL");
        DB::statement("ALTER TABLE report MODIFY COLUMN Penyebab_Gangguan VARCHAR(50) NULL");


        DB::table('report')
            ->where('Penyelesaian_Gangguan', 'Permanent')
            ->update(['Penyelesaian_Gangguan' => 'Permanen']);
            
        DB::table('report')
            ->where('Penyelesaian_Gangguan', 'Temporary')
            ->update(['Penyelesaian_Gangguan' => 'Temporer']);


        DB::table('report')
            ->where('Tipe_Kabel', 'Network')
            ->update(['Tipe_Kabel' => 'Jaringan']);
            
        DB::table('report')
            ->where('Tipe_Kabel', 'Access')
            ->update(['Tipe_Kabel' => 'Akses']);

    
        DB::table('report')
            ->where('Penyebab_Gangguan', 'Vandalism')
            ->update(['Penyebab_Gangguan' => 'Vandalisme']);
            
        DB::table('report')
            ->where('Penyebab_Gangguan', 'Animal Disturbance')
            ->update(['Penyebab_Gangguan' => 'Gangguan Hewan']);
            
        DB::table('report')
            ->where('Penyebab_Gangguan', 'Third Party Activity')
            ->update(['Penyebab_Gangguan' => 'Aktivitas Pihak Ketiga']);
            
        DB::table('report')
            ->where('Penyebab_Gangguan', 'Natural Disturbance')
            ->update(['Penyebab_Gangguan' => 'Gangguan Alam']);
            
        DB::table('report')
            ->where('Penyebab_Gangguan', 'Electrical Issue')
            ->update(['Penyebab_Gangguan' => 'Masalah Listrik']);
            
        DB::table('report')
            ->where('Penyebab_Gangguan', 'Traffic Accident')
            ->update(['Penyebab_Gangguan' => 'Kecelakaan Lalu Lintas']);

        
        DB::statement("ALTER TABLE report MODIFY COLUMN Penyelesaian_Gangguan ENUM('Permanen', 'Temporer') NULL");
        
        DB::statement("ALTER TABLE report MODIFY COLUMN Tipe_Kabel ENUM('Jaringan', 'Akses') NULL");
        
        DB::statement("ALTER TABLE report MODIFY COLUMN Penyebab_Gangguan ENUM(
            'Vandalisme',
            'Gangguan Hewan',
            'Aktivitas Pihak Ketiga',
            'Gangguan Alam',
            'Masalah Listrik',
            'Kecelakaan Lalu Lintas'
        ) NULL");
    }

    public function down(): void
    {
        
        DB::statement("ALTER TABLE report MODIFY COLUMN Penyelesaian_Gangguan VARCHAR(20) NULL");
        DB::statement("ALTER TABLE report MODIFY COLUMN Tipe_Kabel VARCHAR(20) NULL");
        DB::statement("ALTER TABLE report MODIFY COLUMN Penyebab_Gangguan VARCHAR(50) NULL");

        DB::table('report')
            ->where('Penyelesaian_Gangguan', 'Permanen')
            ->update(['Penyelesaian_Gangguan' => 'Permanent']);
            
        DB::table('report')
            ->where('Penyelesaian_Gangguan', 'Temporer')
            ->update(['Penyelesaian_Gangguan' => 'Temporary']);

        DB::table('report')
            ->where('Tipe_Kabel', 'Jaringan')
            ->update(['Tipe_Kabel' => 'Network']);
            
        DB::table('report')
            ->where('Tipe_Kabel', 'Akses')
            ->update(['Tipe_Kabel' => 'Access']);

        DB::table('report')
            ->where('Penyebab_Gangguan', 'Vandalisme')
            ->update(['Penyebab_Gangguan' => 'Vandalism']);
            
        DB::table('report')
            ->where('Penyebab_Gangguan', 'Gangguan Hewan')
            ->update(['Penyebab_Gangguan' => 'Animal Disturbance']);
            
        DB::table('report')
            ->where('Penyebab_Gangguan', 'Aktivitas Pihak Ketiga')
            ->update(['Penyebab_Gangguan' => 'Third Party Activity']);
            
        DB::table('report')
            ->where('Penyebab_Gangguan', 'Gangguan Alam')
            ->update(['Penyebab_Gangguan' => 'Natural Disturbance']);
            
        DB::table('report')
            ->where('Penyebab_Gangguan', 'Masalah Listrik')
            ->update(['Penyebab_Gangguan' => 'Electrical Issue']);
            
        DB::table('report')
            ->where('Penyebab_Gangguan', 'Kecelakaan Lalu Lintas')
            ->update(['Penyebab_Gangguan' => 'Traffic Accident']);

        
        DB::statement("ALTER TABLE report MODIFY COLUMN Penyelesaian_Gangguan ENUM('Permanent', 'Temporary') NULL");
        
        DB::statement("ALTER TABLE report MODIFY COLUMN Tipe_Kabel ENUM('Network', 'Access') NULL");
        
        DB::statement("ALTER TABLE report MODIFY COLUMN Penyebab_Gangguan ENUM(
            'Vandalism',
            'Animal Disturbance',
            'Third Party Activity',
            'Natural Disturbance',
            'Electrical Issue',
            'Traffic Accident'
        ) NULL");
    }
};