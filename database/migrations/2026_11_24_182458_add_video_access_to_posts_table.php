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
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('video_public')->default(true)->after('vkVideo');
            $table->json('video_allowed_roles')->nullable()->after('video_public');
            $table->json('video_allowed_genders')->nullable()->after('video_allowed_roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['video_public', 'video_allowed_roles', 'video_allowed_genders']);
        });
    }
};
