<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->index('deadline');
        });

        Schema::table('krs', function (Blueprint $table) {
            $table->index(['siswa_id', 'mata_kuliah_id', 'semester'], 'krs_siswa_matkul_semester_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropIndex(['deadline']);
        });

        Schema::table('krs', function (Blueprint $table) {
            $table->dropIndex('krs_siswa_matkul_semester_index');
        });
    }
};
