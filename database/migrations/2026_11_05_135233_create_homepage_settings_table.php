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
        Schema::create('homepage_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'birthdays' или 'deceased'
            $table->string('title');
            $table->string('greeting_text')->nullable();
            $table->string('text_color')->default('#ffffff');
            $table->string('bg_color')->default('#1e1b4b');
            $table->string('font_family')->default('sans-serif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_settings');
    }
};
