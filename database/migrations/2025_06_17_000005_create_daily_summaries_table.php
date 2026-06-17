<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('total_tasks');
            $table->integer('done_count');
            $table->integer('skipped_count');
            $table->integer('skipped_auto_count');
            $table->integer('skip_quota');
            $table->integer('over_skip');
            $table->integer('saved_skip');
            $table->integer('hp_before');
            $table->integer('hp_change');
            $table->integer('hp_after');
            $table->bigInteger('xp_earned');
            $table->integer('base_xp');
            $table->decimal('perf_multiplier', 4, 2);
            $table->decimal('streak_multiplier', 4, 2);
            $table->integer('streak_before');
            $table->integer('streak_after');
            $table->boolean('shield_used')->default(false);
            $table->boolean('debt_added')->default(false);
            $table->boolean('debt_cleared')->default(false);
            $table->boolean('streak_reset')->default(false);
            $table->decimal('pct_completed', 5, 2);
            $table->enum('closed_by', ['user', 'system']);
            $table->timestamp('closed_at');
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_summaries');
    }
};
