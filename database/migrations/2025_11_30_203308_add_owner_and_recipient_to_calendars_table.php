<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add new columns without foreign keys first
        Schema::table('calendars', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->nullable()->after('id');
            $table->unsignedBigInteger('recipient_id')->nullable()->after('owner_id');
        });

        // Step 2: Migrate existing data: copy user_id to owner_id
        DB::table('calendars')->update([
            'owner_id' => DB::raw('user_id'),
        ]);

        // Step 3: Make owner_id not nullable
        Schema::table('calendars', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->nullable(false)->change();
        });

        // Step 4: Drop the old user_id foreign key constraint and column
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        // Step 5: Add foreign key constraints
        Schema::table('calendars', function (Blueprint $table) {
            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('recipient_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Drop foreign key constraints
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropForeign(['recipient_id']);
        });

        // Step 2: Add back user_id without constraint first
        Schema::table('calendars', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });

        // Step 3: Migrate data back: copy owner_id to user_id
        DB::table('calendars')->update([
            'user_id' => DB::raw('owner_id'),
        ]);

        // Step 4: Make user_id not nullable
        Schema::table('calendars', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        // Step 5: Add foreign key constraint
        Schema::table('calendars', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // Step 6: Drop owner_id and recipient_id
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropColumn(['owner_id', 'recipient_id']);
        });
    }
};
