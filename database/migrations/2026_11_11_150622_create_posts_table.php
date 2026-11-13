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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('content');
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('conference_id')->constrained('conferences')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->string('thumbnail')->nullable();
            $table->string('youtube')->nullable();
            $table->string('rutube')->nullable();
            $table->string('dzen')->nullable();
            $table->string('vkVideo')->nullable();

            // Аудио
            $table->string('audio_file')->nullable();
            $table->string('audio_filename')->nullable();
            $table->bigInteger('audio_size')->nullable();
            $table->string('audio_mime')->nullable();

            // Текст
            $table->string('text')->nullable(); // Скорее всего ссылка или сам текст? В ТЗ сказано "text varchar", оставим как есть.
            $table->string('text_file')->nullable();
            $table->string('text_filename')->nullable();
            $table->bigInteger('text_size')->nullable();
            $table->string('text_mime')->nullable();

            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->timestamps();

            // Индексы для фильтрации
            $table->index(['category_id', 'group_id', 'conference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
