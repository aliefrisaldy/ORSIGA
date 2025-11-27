<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_reports', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->unsignedBigInteger('site_id');
            $table->enum('status', ['Open', 'Close'])->default('Open');
            $table->timestamps();

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            
            $table->index('ticket_number');
            $table->index('site_id');
            $table->index('status');    
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_reports');
    }
};