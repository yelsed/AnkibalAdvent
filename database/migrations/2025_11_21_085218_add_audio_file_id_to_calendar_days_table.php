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
        Schema::table('calendar_days', function (Blueprint $table) {
            $table->foreignId('audio_file_id')->nullable()->after('audio_url')->constrained('audio_files')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_days', function (Blueprint $table) {
            $table->dropForeign(['audio_file_id']);
            $table->dropColumn('audio_file_id');
        });
    }
};
