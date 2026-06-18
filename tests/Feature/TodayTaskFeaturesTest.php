<?php

namespace Tests\Feature;

use App\Models\TaskInstance;
use App\Models\TaskTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodayTaskFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_with_due_time_is_marked_overdue_after_deadline(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-18 07:00:00'));

        $user = User::factory()->create(['hp' => 0, 'xp' => 0, 'level' => 1]);
        $template = TaskTemplate::create([
            'user_id' => $user->id,
            'title' => 'Dậy 6h',
            'priority' => 'high',
            'recurrence_type' => 'daily',
            'start_date' => '2026-06-18',
            'due_time' => '06:00',
            'is_active' => true,
        ]);

        $instance = TaskInstance::create([
            'task_template_id' => $template->id,
            'user_id' => $user->id,
            'scheduled_date' => '2026-06-18',
            'status' => 'pending',
        ]);

        $instance->load('template');

        $this->assertTrue($instance->isOverdue());

        Carbon::setTestNow();
    }

    public function test_quick_task_creates_one_time_instance_for_today(): void
    {
        $user = User::factory()->create(['hp' => 0, 'xp' => 0, 'level' => 1]);
        $today = Carbon::today()->toDateString();

        $this->actingAs($user)
            ->postJson(route('web_api.today.quick_task'), [
                'title' => 'Việc gấp',
                'due_time' => '18:30',
                'priority' => 'high',
            ])
            ->assertOk();

        $this->assertDatabaseHas('task_templates', [
            'user_id' => $user->id,
            'title' => 'Việc gấp',
            'recurrence_type' => 'one_time',
        ]);

        $template = TaskTemplate::where('title', 'Việc gấp')->first();
        $this->assertSame('18:30', substr((string) $template->due_time, 0, 5));

        $this->assertEquals(1, TaskInstance::where('user_id', $user->id)->whereDate('scheduled_date', $today)->count());
    }

    public function test_dashboard_puts_pending_tasks_before_completed_ones(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-18 10:00:00'));

        $user = User::factory()->create(['hp' => 0, 'xp' => 0, 'level' => 1]);

        $template = TaskTemplate::create([
            'user_id' => $user->id,
            'title' => 'Pending task',
            'priority' => 'medium',
            'recurrence_type' => 'daily',
            'start_date' => '2026-06-18',
            'is_active' => true,
        ]);

        $doneTemplate = TaskTemplate::create([
            'user_id' => $user->id,
            'title' => 'Done task',
            'priority' => 'medium',
            'recurrence_type' => 'daily',
            'start_date' => '2026-06-18',
            'is_active' => true,
        ]);

        TaskInstance::create([
            'task_template_id' => $template->id,
            'user_id' => $user->id,
            'scheduled_date' => '2026-06-18',
            'status' => 'pending',
        ]);

        TaskInstance::create([
            'task_template_id' => $doneTemplate->id,
            'user_id' => $user->id,
            'scheduled_date' => '2026-06-18',
            'status' => 'done',
            'completed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('instances.0.status', 'pending')
                ->where('instances.1.status', 'done'));

        Carbon::setTestNow();
    }
}
