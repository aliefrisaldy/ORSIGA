<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('repair_report_id'); 
            $table->unsignedBigInteger('related_repair_report_id'); 
            $table->timestamps();

            $table->foreign('repair_report_id')
                  ->references('id_repair_reports')
                  ->on('repair_reports')
                  ->onDelete('cascade');
                  
            $table->foreign('related_repair_report_id')
                  ->references('id_repair_reports')
                  ->on('repair_reports')
                  ->onDelete('cascade');
            
            // ✅ Gunakan nama custom yang pendek
            $table->unique(['repair_report_id', 'related_repair_report_id'], 'unique_repair_relation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_relations');
    }
};