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
        Schema::table('users', function (Blueprint $table) {
            // Мемориальные данные
            $table->date('death_date')->nullable()->after('is_deceased');
            $table->date('funeral_date')->nullable()->after('death_date');

            // Стили для именинника (у каждого свои, если нужно)
            $table->string('birthday_text_color')->nullable()->default('#ffffff');
            $table->string('birthday_bg_color')->nullable()->default('#2e1065');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
