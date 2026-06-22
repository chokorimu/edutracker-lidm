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
        Schema::create('ipk_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('user_siswa')->onDelete('cascade');
            $table->float('ipk');
            $table->unsignedTinyInteger('semester');
            $table->string('tahun_ajaran');
            $table->unsignedInteger('total_sks');
            $table->unsignedTinyInteger('rekomendasi_sks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ipk_history');
    }
};
