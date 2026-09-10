<?php

namespace Tests\Feature;

use App\Actions\GenerateDailyTaskInstancesAction;
use App\Models\DailySummary;
use App\Models\TaskInstance;
use App\Models\TaskTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
        $this->assertEquals(1, TaskInstance::where('user_id', $user->id)
            ->whereDate('scheduled_date', $date)
            ->where('status', 'pending')
            ->count());
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

    public function test_backfill_creates_missing_yesterday_when_running_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-19 09:00:00'));

        $user = User::factory()->create();
        $template = TaskTemplate::create([
            'user_id' => $user->id,
            'title' => 'Daily task',
            'priority' => 'medium',
            'recurrence_type' => 'daily',
            'start_date' => '2026-07-17',
            'is_active' => true,
        ]);

        // Day 17 exists; day 18 was skipped (cron/user miss) — must be backfilled on day 19.
        TaskInstance::create([
            'task_template_id' => $template->id,
            'user_id' => $user->id,
            'scheduled_date' => '2026-07-17',
            'status' => 'pending',
        ]);

        $created = app(GenerateDailyTaskInstancesAction::class)
            ->executeWithBackfill(Carbon::today(), $user->id);

        $this->assertEquals(2, $created);
        $this->assertEquals(1, TaskInstance::where('user_id', $user->id)
            ->where('task_template_id', $template->id)
            ->whereDate('scheduled_date', '2026-07-18')
            ->count());
        $this->assertEquals(1, TaskInstance::where('user_id', $user->id)
            ->where('task_template_id', $template->id)
            ->whereDate('scheduled_date', '2026-07-19')
            ->count());

        Carbon::setTestNow();
    }

    public function test_backfill_starts_after_last_closed_summary(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-19 09:00:00'));

        $user = User::factory()->create();
        TaskTemplate::create([
            'user_id' => $user->id,
            'title' => 'Daily task',
            'priority' => 'medium',
            'recurrence_type' => 'daily',
            'start_date' => '2026-07-15',
            'is_active' => true,
        ]);

        DailySummary::create([
            'user_id' => $user->id,
            'date' => '2026-07-17',
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
            'closed_by' => 'system',
            'closed_at' => now(),
        ]);

        $created = app(GenerateDailyTaskInstancesAction::class)
            ->executeWithBackfill(Carbon::today(), $user->id);

        $this->assertEquals(2, $created);
        $this->assertEquals(0, TaskInstance::where('user_id', $user->id)
            ->whereDate('scheduled_date', '2026-07-17')
            ->count());
        $this->assertEquals(1, TaskInstance::where('user_id', $user->id)
            ->whereDate('scheduled_date', '2026-07-18')
            ->count());
        $this->assertEquals(1, TaskInstance::where('user_id', $user->id)
            ->whereDate('scheduled_date', '2026-07-19')
            ->count());

        Carbon::setTestNow();
    }

    public function test_dashboard_backfills_missing_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-19 09:00:00'));

        $user = User::factory()->create([
            'hp' => 0,
            'xp' => 0,
            'level' => 1,
        ]);
        $template = TaskTemplate::create([
            'user_id' => $user->id,
            'title' => 'Uống nước',
            'priority' => 'medium',
            'recurrence_type' => 'daily',
            'start_date' => '2026-07-17',
            'is_active' => true,
        ]);

        TaskInstance::create([
            'task_template_id' => $template->id,
            'user_id' => $user->id,
            'scheduled_date' => '2026-07-17',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();

        $this->assertEquals(1, TaskInstance::where('user_id', $user->id)
            ->whereDate('scheduled_date', '2026-07-18')
            ->count());
        $this->assertEquals(1, TaskInstance::where('user_id', $user->id)
            ->whereDate('scheduled_date', '2026-07-19')
            ->count());

        Carbon::setTestNow();
    }
}
