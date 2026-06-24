<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_siswa', function (Blueprint $table) {
            $table->boolean('profile_completed')->default(false)->after('semester');
        });
    }

    public function down(): void
    {
        Schema::table('user_siswa', function (Blueprint $table) {
            $table->dropColumn('profile_completed');
        });
    }
};
