<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'admin'])->default('user')->after('password');
            $table->integer('hp')->default(0)->after('role');
            $table->bigInteger('xp')->default(0)->after('hp');
            $table->integer('level')->default(1)->after('xp');
            $table->integer('streak_count')->default(0)->after('level');
            $table->date('streak_last_date')->nullable()->after('streak_count');
            $table->integer('shield_count')->default(0)->after('streak_last_date');
            $table->integer('debt_count')->default(0)->after('shield_count');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'hp', 'xp', 'level', 'streak_count',
                'streak_last_date', 'shield_count', 'debt_count',
            ]);
        });
    }
};
