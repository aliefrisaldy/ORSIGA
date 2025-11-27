<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('repair_reports', function (Blueprint $table) {
            $table->bigIncrements('id_repair_reports');
            $table->string('ticket_number')->unique();
            $table->string('technician_name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->json('documentation')->nullable();
            $table->longText('work_details')->nullable();
            $table->timestamps();
        });

        // Tambahkan kolom ENUM menggunakan raw SQL untuk kontrol penuh
        DB::statement("ALTER TABLE repair_reports ADD COLUMN repair_type ENUM('Permanent', 'Temporary') NULL AFTER work_details");
        
        DB::statement("ALTER TABLE repair_reports ADD COLUMN cable_type ENUM('Network', 'Access') NULL AFTER repair_type");
        
        DB::statement("ALTER TABLE repair_reports ADD COLUMN disruption_cause ENUM(
            'Vandalism',
            'Animal Disturbance',
            'Third Party Activity',
            'Natural Disturbance',
            'Electrical Issue',
            'Traffic Accident'
        ) NULL AFTER cable_type");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_reports');
    }
};