<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report', function (Blueprint $table) {
            $table->enum('Penyebab_Gangguan', [
                'Vandalism',
                'Animal Disturbance',
                'Third Party Activity',
                'Natural Disturbance',
                'Electrical Issue',
                'Traffic Accident'
            ])->nullable()->after('Tipe_Kabel');
        });
    }

    public function down(): void
    {
        Schema::table('report', function (Blueprint $table) {
            $table->dropColumn('Penyebab_Gangguan');
        });
    }
};
