<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_stats', function (Blueprint $table) {
            $table->string('guest_ip', 45)
                ->nullable()
                ->virtualAs('CASE WHEN user_id IS NULL THEN ip ELSE NULL END');
        });

        Schema::table('post_stats', function (Blueprint $table) {
            $table->unique(
                ['post_id', 'guest_ip', 'viewed_at_date'],
                'unique_guest_ip_view'
            );
        });

        Schema::table('post_stats', function (Blueprint $table) {
            $table->dropUnique('unique_ip_view');
        });
    }

    public function down(): void
    {
        Schema::table('post_stats', function (Blueprint $table) {
            $table->unique(['post_id', 'ip', 'viewed_at_date'], 'unique_ip_view');
        });

        Schema::table('post_stats', function (Blueprint $table) {
            $table->dropUnique('unique_guest_ip_view');
        });

        Schema::table('post_stats', function (Blueprint $table) {
            $table->dropColumn('guest_ip');
        });
    }
};
