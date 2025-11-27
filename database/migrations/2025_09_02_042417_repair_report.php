<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            
            // Gunakan string biasa untuk SQLite, Laravel akan handle sebagai ENUM di MySQL
            $table->enum('repair_type', ['Permanent', 'Temporary'])->nullable();
            
            $table->enum('cable_type', ['Network', 'Access'])->nullable();
            
            $table->enum('disruption_cause', [
                'Vandalism',
                'Animal Disturbance',
                'Third Party Activity',
                'Natural Disturbance',
                'Electrical Issue',
                'Traffic Accident'
            ])->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_reports');
    }
};