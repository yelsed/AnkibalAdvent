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
        Schema::table('calendars', function (Blueprint $table) {
            $table->string('theme_type')->default('single')->after('theme_color');
            $table->string('secondary_color')->nullable()->after('theme_type');
            $table->json('seasonal_config')->nullable()->after('secondary_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropColumn(['theme_type', 'secondary_color', 'seasonal_config']);
        });
    }
};
