<?php

namespace Tests\Feature;

use App\Actions\CloseDaySummaryAction;
use App\Models\DailySummary;
use App\Models\TaskInstance;
use App\Models\TaskTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloseDaySummaryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_closes_day_and_auto_skips_pending_tasks(): void
    {
        $user = User::factory()->create(['hp' => 10, 'xp' => 0, 'streak_count' => 0]);
        $date = Carbon::parse('2025-06-17');

        $templateA = TaskTemplate::create([
            'user_id' => $user->id,
            'title' => 'Task A',
            'priority' => 'medium',
            'recurrence_type' => 'daily',
            'start_date' => $date,
            'is_active' => true,
        ]);

        $templateB = TaskTemplate::create([
            'user_id' => $user->id,
            'title' => 'Task B',
            'priority' => 'medium',
            'recurrence_type' => 'daily',
            'start_date' => $date,
            'is_active' => true,
        ]);

        TaskInstance::create([
            'task_template_id' => $templateA->id,
            'user_id' => $user->id,
            'scheduled_date' => $date->toDateString(),
            'status' => 'done',
        ]);

        TaskInstance::create([
            'task_template_id' => $templateB->id,
            'user_id' => $user->id,
            'scheduled_date' => $date->toDateString(),
            'status' => 'pending',
        ]);

        $summary = app(CloseDaySummaryAction::class)->execute($user->id, $date);

        $this->assertEquals(2, $summary->total_tasks);
        $this->assertEquals(1, $summary->done_count);
        $this->assertEquals(1, $summary->skipped_auto_count);
        $this->assertEquals(50.0, (float) $summary->pct_completed);
        $this->assertDatabaseHas('daily_summaries', [
            'user_id' => $user->id,
        ]);
        $this->assertTrue(
            DailySummary::where('user_id', $user->id)->whereDate('date', $date)->exists()
        );
    }

    public function test_does_not_create_duplicate_summary(): void
    {
        $user = User::factory()->create();
        $date = Carbon::parse('2025-06-17');

        DailySummary::create([
            'user_id' => $user->id,
            'date' => $date->toDateString(),
            'total_tasks' => 0,
            'done_count' => 0,
            'skipped_count' => 0,
            'skipped_auto_count' => 0,
            'skip_quota' => 0,
            'over_skip' => 0,
            'saved_skip' => 0,
            'hp_before' => 0,
            'hp_change' => 0,
            'hp_after' => 0,
            'xp_earned' => 0,
            'base_xp' => 0,
            'perf_multiplier' => 0,
            'streak_multiplier' => 0,
            'streak_before' => 0,
            'streak_after' => 0,
            'pct_completed' => 0,
            'closed_by' => 'user',
            'closed_at' => now(),
        ]);

        $result = app(CloseDaySummaryAction::class)->execute($user->id, $date);

        $this->assertEquals(1, DailySummary::where('user_id', $user->id)->count());
        $this->assertEquals(0, $result->total_tasks);
    }
}
