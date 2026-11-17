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
        Schema::create('bible_verses', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('book_id');
            $table->string('book_name', 50);
            $table->unsignedSmallInteger('chapter_id');
            $table->unsignedSmallInteger('verse_id');
            $table->text('text');
            $table->timestamps();

            $table->unique(['book_id', 'chapter_id', 'verse_id']);
            $table->index(['book_id', 'chapter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bible_verses');
    }
};
