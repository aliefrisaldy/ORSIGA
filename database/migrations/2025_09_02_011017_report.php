<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report', function (Blueprint $table) {
            $table->bigIncrements('id_report');
            $table->string('No_Tiket')->unique();
            $table->string('Nama_Teknisi');
            $table->decimal('Latitude', 10, 7);
            $table->decimal('Longitude', 10, 7);
            $table->json('Dokumentasi')->nullable();
            $table->longText('Detail_Pekerjaan')->nullable();
            $table->enum('Penyelesaian_Gangguan', ['Permanent', 'Temporary'])->nullable();
            $table->enum('Tipe_Kabel', ['Network', 'Access'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report');
    }
};
