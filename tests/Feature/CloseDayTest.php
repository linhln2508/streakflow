<?php

namespace Tests\Feature;

use App\Models\DailySummary;
use App\Models\TaskInstance;
use App\Models\TaskTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloseDayTest extends TestCase
{
    use RefreshDatabase;

    protected function createInstance(User $user, string $date, string $status = 'pending'): void
    {
        $template = TaskTemplate::create([
            'user_id' => $user->id,
            'title' => 'Task '.$date,
            'priority' => 'medium',
            'recurrence_type' => 'one_time',
            'recurrence_config' => ['date' => $date],
            'start_date' => $date,
            'is_active' => true,
        ]);

        TaskInstance::create([
            'task_template_id' => $template->id,
            'user_id' => $user->id,
            'scheduled_date' => $date,
            'status' => $status,
        ]);
    }

    public function test_dashboard_lists_unclosed_days(): void
    {
        $user = User::factory()->create();
        $yesterday = Carbon::yesterday()->toDateString();
        $this->createInstance($user, $yesterday);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->has('unclosedDays', 1)
                ->where('unclosedDays.0.date', $yesterday)
                ->where('selectedDate', $yesterday));
    }

    public function test_dashboard_can_open_specific_unclosed_day(): void
    {
        $user = User::factory()->create();
        $yesterday = Carbon::yesterday()->toDateString();
        $this->createInstance($user, $yesterday);

        $this->actingAs($user)
            ->get(route('dashboard', ['date' => $yesterday]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('selectedDate', $yesterday)
                ->where('isToday', false));
    }

    public function test_user_can_close_past_day(): void
    {
        $user = User::factory()->create(['hp' => 50, 'xp' => 0, 'streak_count' => 0]);
        $date = Carbon::yesterday()->toDateString();
        $this->createInstance($user, $date, 'done');

        $this->actingAs($user)
            ->postJson(route('web_api.today.close'), ['date' => $date])
            ->assertOk()
            ->assertJsonPath('data.date', $date);

        $this->assertTrue(
            DailySummary::where('user_id', $user->id)->whereDate('date', $date)->exists()
        );
        $this->assertSame('user', DailySummary::first()->closed_by);
    }

    public function test_cannot_close_future_day(): void
    {
        $user = User::factory()->create();
        $future = Carbon::tomorrow()->toDateString();

        $this->actingAs($user)
            ->postJson(route('web_api.today.close'), ['date' => $future])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function test_closed_day_not_in_unclosed_list(): void
    {
        $user = User::factory()->create();
        $date = Carbon::yesterday()->toDateString();
        $this->createInstance($user, $date, 'done');

        DailySummary::create([
            'user_id' => $user->id,
            'date' => $date,
            'total_tasks' => 1,
            'done_count' => 1,
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
            'streak_multiplier' => 1,
            'streak_before' => 0,
            'streak_after' => 0,
            'shield_used' => false,
            'debt_added' => false,
            'debt_cleared' => false,
            'streak_reset' => false,
            'pct_completed' => 100,
            'closed_by' => 'user',
            'closed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page->where('unclosedDays', []));
    }

    public function test_legacy_close_days_url_redirects_to_dashboard(): void
    {
        $user = User::factory()->create();
        $date = Carbon::yesterday()->toDateString();

        $this->actingAs($user)
            ->get('/close-days/'.$date)
            ->assertRedirect(route('dashboard', ['date' => $date]));
    }
}
