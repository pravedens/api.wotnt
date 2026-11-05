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
        Schema::table('holidays', function (Blueprint $table) {
            $table->string('text_color')->nullable()->default('#ffffff');
            $table->string('bg_color')->nullable()->default('#1e1b4b');
            $table->string('font_family')->nullable()->default('sans-serif');
            $table->string('title_font_family')->nullable()->default('sans-serif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            //
        });
    }
};
