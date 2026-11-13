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
        Schema::create('post_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();

            // ✅ Пользователь (nullable, т.к. гости тоже могут просматривать/лайкать)
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();

            $table->string('ip', 45);
            $table->string('user_agent', 500);
            $table->string('fingerprint', 100)->nullable();
            $table->boolean('viewed')->default(false);
            $table->boolean('liked')->default(false);
            $table->timestamp('viewed_at')->nullable();
            $table->date('viewed_at_date')->nullable();
            $table->timestamps();

            // ✅ Уникальность просмотра: 1 раз в день на пользователя (авторизованного)
            // или 1 раз в день на IP (гостя)
            $table->unique(['post_id', 'user_id', 'viewed_at_date'], 'unique_user_view');
            $table->unique(['post_id', 'ip', 'viewed_at_date'], 'unique_ip_view');

            // ✅ Индекс для быстрого поиска лайка
            $table->index(['post_id', 'user_id', 'liked']);
            $table->index(['post_id', 'ip', 'liked']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_stats');
    }
};
