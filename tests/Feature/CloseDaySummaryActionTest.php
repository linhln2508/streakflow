<?php

namespace Tests\Feature;

use App\Actions\CloseDaySummaryAction;
use App\Actions\GenerateDailyTaskInstancesAction;
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

class GenerateDailyTaskInstancesActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_instances_for_active_templates(): void
    {
        $user = User::factory()->create();
        $date = Carbon::parse('2025-06-17');

        TaskTemplate::create([
            'user_id' => $user->id,
            'title' => 'Daily task',
            'priority' => 'medium',
            'recurrence_type' => 'daily',
            'start_date' => $date->copy()->subDay(),
            'is_active' => true,
        ]);

        $created = app(GenerateDailyTaskInstancesAction::class)->execute($date);

        $this->assertEquals(1, $created);
        $this->assertDatabaseHas('task_instances', [
            'user_id' => $user->id,
            'scheduled_date' => $date->toDateString(),
            'status' => 'pending',
        ]);
    }

    public function test_deactivates_one_time_template_after_generation(): void
    {
        $user = User::factory()->create();
        $date = Carbon::parse('2025-06-20');

        $template = TaskTemplate::create([
            'user_id' => $user->id,
            'title' => 'One time',
            'priority' => 'medium',
            'recurrence_type' => 'one_time',
            'recurrence_config' => ['date' => '2025-06-20'],
            'start_date' => $date,
            'is_active' => true,
        ]);

        app(GenerateDailyTaskInstancesAction::class)->execute($date);

        $this->assertFalse($template->fresh()->is_active);
    }
}
