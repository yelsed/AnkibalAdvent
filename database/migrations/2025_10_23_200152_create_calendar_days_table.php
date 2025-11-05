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
        Schema::create('calendar_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_number');
            $table->enum('gift_type', ['text', 'image_text', 'product']);
            $table->string('title')->nullable();
            $table->text('content_text')->nullable();
            $table->string('content_image_path')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();

            $table->unique(['calendar_id', 'day_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_days');
    }
};
