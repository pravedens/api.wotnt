<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name');
            $table->string('middle_name')->nullable()->after('last_name');
            $table->string('phone')->nullable()->after('email_verified_at');
            $table->string('city')->nullable();
            $table->string('church_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('about')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('avatar_path')->nullable();

            // Флаг согласия на обработку ПДн (152-ФЗ)
            $table->boolean('consent_pd')->default(false);
            $table->timestamp('consent_pd_at')->nullable();
            $table->string('consent_pd_ip', 45)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_name', 'middle_name', 'phone', 'city',
                'church_name', 'birth_date', 'about',
                'marital_status', 'gender', 'avatar_path',
                'consent_pd', 'consent_pd_at', 'consent_pd_ip'
            ]);
        });
    }
};
