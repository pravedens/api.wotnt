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
        Schema::create('daily_verses', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('book_id')->nullable();
            $table->unsignedSmallInteger('chapter_id')->nullable();
            $table->unsignedSmallInteger('verse_id')->nullable();
            $table->unsignedSmallInteger('verse_to_id')->nullable();
            $table->string('reference');
            $table->text('text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_verses');
    }
};
