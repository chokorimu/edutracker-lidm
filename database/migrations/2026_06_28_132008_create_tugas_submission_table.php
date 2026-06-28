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
        Schema::create('tugas_submission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('user_siswa')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_name');
            $table->timestamp('submitted_at');
            $table->enum('status', ['submitted', 'late'])->default('submitted');
            $table->unique(['tugas_id', 'siswa_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas_submission');
    }
};
