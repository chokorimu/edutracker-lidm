<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing indexes to reduce full table scans under load.
     *
     * Covers the hottest query patterns:
     * - Prodi dashboard: notifikasi WHERE tipe + created_at (3 count queries)
     * - Siswa dashboard: notifikasi WHERE siswa_id ORDER BY created_at
     * - Dosen dashboard: notifikasi_dosen WHERE dosen_id ORDER BY created_at
     * - Nilai lookups: nilai_tugas WHERE tugas_id + siswa_id (composite)
     * - Weekly load: tugas WHERE mata_kuliah_id + deadline (composite)
     * - IPK history: ipk_history WHERE siswa_id ORDER BY semester
     */
    public function up(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->index(['tipe', 'created_at'], 'notifikasi_tipe_created_index');
            $table->index(['siswa_id', 'created_at'], 'notifikasi_siswa_created_index');
        });

        Schema::table('notifikasi_dosen', function (Blueprint $table) {
            $table->index(['dosen_id', 'created_at'], 'notifikasi_dosen_dosen_created_index');
        });

        Schema::table('nilai_tugas', function (Blueprint $table) {
            $table->unique(['tugas_id', 'siswa_id'], 'nilai_tugas_tugas_siswa_unique');
        });

        Schema::table('tugas', function (Blueprint $table) {
            $table->index(['mata_kuliah_id', 'deadline'], 'tugas_matkul_deadline_index');
        });

        Schema::table('ipk_history', function (Blueprint $table) {
            $table->index(['siswa_id', 'semester'], 'ipk_history_siswa_semester_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->dropIndex('notifikasi_tipe_created_index');
            $table->dropIndex('notifikasi_siswa_created_index');
        });

        Schema::table('notifikasi_dosen', function (Blueprint $table) {
            $table->dropIndex('notifikasi_dosen_dosen_created_index');
        });

        Schema::table('nilai_tugas', function (Blueprint $table) {
            $table->dropIndex('nilai_tugas_tugas_siswa_unique');
        });

        Schema::table('tugas', function (Blueprint $table) {
            $table->dropIndex('tugas_matkul_deadline_index');
        });

        Schema::table('ipk_history', function (Blueprint $table) {
            $table->dropIndex('ipk_history_siswa_semester_index');
        });
    }
};
