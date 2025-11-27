<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Rename tabel dari 'report' ke 'repair_reports'
        Schema::rename('report', 'repair_reports');
        
        // 2. Ubah ENUM ke VARCHAR sementara untuk update data
        DB::statement("ALTER TABLE repair_reports MODIFY COLUMN Penyelesaian_Gangguan VARCHAR(20) NULL");
        DB::statement("ALTER TABLE repair_reports MODIFY COLUMN Tipe_Kabel VARCHAR(20) NULL");
        DB::statement("ALTER TABLE repair_reports MODIFY COLUMN Penyebab_Gangguan VARCHAR(50) NULL");

        // 3. Update data dari Indonesia ke Inggris
        DB::table('repair_reports')
            ->where('Penyelesaian_Gangguan', 'Permanen')
            ->update(['Penyelesaian_Gangguan' => 'Permanent']);
            
        DB::table('repair_reports')
            ->where('Penyelesaian_Gangguan', 'Temporer')
            ->update(['Penyelesaian_Gangguan' => 'Temporary']);

        DB::table('repair_reports')
            ->where('Tipe_Kabel', 'Jaringan')
            ->update(['Tipe_Kabel' => 'Network']);
            
        DB::table('repair_reports')
            ->where('Tipe_Kabel', 'Akses')
            ->update(['Tipe_Kabel' => 'Access']);

        DB::table('repair_reports')
            ->where('Penyebab_Gangguan', 'Vandalisme')
            ->update(['Penyebab_Gangguan' => 'Vandalism']);
            
        DB::table('repair_reports')
            ->where('Penyebab_Gangguan', 'Gangguan Hewan')
            ->update(['Penyebab_Gangguan' => 'Animal Disturbance']);
            
        DB::table('repair_reports')
            ->where('Penyebab_Gangguan', 'Aktivitas Pihak Ketiga')
            ->update(['Penyebab_Gangguan' => 'Third Party Activity']);
            
        DB::table('repair_reports')
            ->where('Penyebab_Gangguan', 'Gangguan Alam')
            ->update(['Penyebab_Gangguan' => 'Natural Disturbance']);
            
        DB::table('repair_reports')
            ->where('Penyebab_Gangguan', 'Masalah Listrik')
            ->update(['Penyebab_Gangguan' => 'Electrical Issue']);
            
        DB::table('repair_reports')
            ->where('Penyebab_Gangguan', 'Kecelakaan Lalu Lintas')
            ->update(['Penyebab_Gangguan' => 'Traffic Accident']);
        
        // 4. Rename columns menggunakan raw SQL (tanpa doctrine/dbal)
        DB::statement('ALTER TABLE repair_reports CHANGE id_report id_repair_reports BIGINT UNSIGNED AUTO_INCREMENT');
        DB::statement('ALTER TABLE repair_reports CHANGE No_Tiket ticket_number VARCHAR(255)');
        DB::statement('ALTER TABLE repair_reports CHANGE Nama_Teknisi technician_name VARCHAR(255)');
        DB::statement('ALTER TABLE repair_reports CHANGE Latitude latitude DECIMAL(10,7)');
        DB::statement('ALTER TABLE repair_reports CHANGE Longitude longitude DECIMAL(10,7)');
        DB::statement('ALTER TABLE repair_reports CHANGE Dokumentasi documentation JSON');
        DB::statement('ALTER TABLE repair_reports CHANGE Detail_Pekerjaan work_details LONGTEXT');
        
        // 5. Ubah kolom ENUM dengan nama baru dan nilai Inggris
        DB::statement("ALTER TABLE repair_reports CHANGE Penyelesaian_Gangguan repair_type ENUM('Permanent', 'Temporary') NULL");
        DB::statement("ALTER TABLE repair_reports CHANGE Tipe_Kabel cable_type ENUM('Network', 'Access') NULL");
        DB::statement("ALTER TABLE repair_reports CHANGE Penyebab_Gangguan disruption_cause ENUM(
            'Vandalism',
            'Animal Disturbance',
            'Third Party Activity',
            'Natural Disturbance',
            'Electrical Issue',
            'Traffic Accident'
        ) NULL");
    }

    public function down(): void
    {
        // 1. Ubah ENUM kembali ke VARCHAR untuk update data
        DB::statement("ALTER TABLE repair_reports MODIFY COLUMN repair_type VARCHAR(20) NULL");
        DB::statement("ALTER TABLE repair_reports MODIFY COLUMN cable_type VARCHAR(20) NULL");
        DB::statement("ALTER TABLE repair_reports MODIFY COLUMN disruption_cause VARCHAR(50) NULL");

        // 2. Update data dari Inggris ke Indonesia
        DB::table('repair_reports')
            ->where('repair_type', 'Permanent')
            ->update(['repair_type' => 'Permanen']);
            
        DB::table('repair_reports')
            ->where('repair_type', 'Temporary')
            ->update(['repair_type' => 'Temporer']);

        DB::table('repair_reports')
            ->where('cable_type', 'Network')
            ->update(['cable_type' => 'Jaringan']);
            
        DB::table('repair_reports')
            ->where('cable_type', 'Access')
            ->update(['cable_type' => 'Akses']);

        DB::table('repair_reports')
            ->where('disruption_cause', 'Vandalism')
            ->update(['disruption_cause' => 'Vandalisme']);
            
        DB::table('repair_reports')
            ->where('disruption_cause', 'Animal Disturbance')
            ->update(['disruption_cause' => 'Gangguan Hewan']);
            
        DB::table('repair_reports')
            ->where('disruption_cause', 'Third Party Activity')
            ->update(['disruption_cause' => 'Aktivitas Pihak Ketiga']);
            
        DB::table('repair_reports')
            ->where('disruption_cause', 'Natural Disturbance')
            ->update(['disruption_cause' => 'Gangguan Alam']);
            
        DB::table('repair_reports')
            ->where('disruption_cause', 'Electrical Issue')
            ->update(['disruption_cause' => 'Masalah Listrik']);
            
        DB::table('repair_reports')
            ->where('disruption_cause', 'Traffic Accident')
            ->update(['disruption_cause' => 'Kecelakaan Lalu Lintas']);

        // 3. Rename kolom ENUM kembali dengan nilai Indonesia
        DB::statement("ALTER TABLE repair_reports CHANGE repair_type Penyelesaian_Gangguan ENUM('Permanen', 'Temporer') NULL");
        DB::statement("ALTER TABLE repair_reports CHANGE cable_type Tipe_Kabel ENUM('Jaringan', 'Akses') NULL");
        DB::statement("ALTER TABLE repair_reports CHANGE disruption_cause Penyebab_Gangguan ENUM(
            'Vandalisme',
            'Gangguan Hewan',
            'Aktivitas Pihak Ketiga',
            'Gangguan Alam',
            'Masalah Listrik',
            'Kecelakaan Lalu Lintas'
        ) NULL");
        
        // 4. Rename columns kembali ke nama lama
        DB::statement('ALTER TABLE repair_reports CHANGE id_repair_reports id_report BIGINT UNSIGNED AUTO_INCREMENT');
        DB::statement('ALTER TABLE repair_reports CHANGE ticket_number No_Tiket VARCHAR(255)');
        DB::statement('ALTER TABLE repair_reports CHANGE technician_name Nama_Teknisi VARCHAR(255)');
        DB::statement('ALTER TABLE repair_reports CHANGE latitude Latitude DECIMAL(10,7)');
        DB::statement('ALTER TABLE repair_reports CHANGE longitude Longitude DECIMAL(10,7)');
        DB::statement('ALTER TABLE repair_reports CHANGE documentation Dokumentasi JSON');
        DB::statement('ALTER TABLE repair_reports CHANGE work_details Detail_Pekerjaan LONGTEXT');
        
        // 5. Rename tabel kembali
        Schema::rename('repair_reports', 'report');
    }
};