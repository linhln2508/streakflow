<?php

namespace Tests\Feature;

use App\Models\TaskTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTaskGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_generates_today_instances_for_active_templates(): void
    {
        $user = User::factory()->create([
            'hp' => 0,
            'xp' => 0,
            'level' => 1,
        ]);
        $today = Carbon::today()->toDateString();

        TaskTemplate::create([
            'user_id' => $user->id,
            'title' => 'Uống nước',
            'priority' => 'medium',
            'recurrence_type' => 'daily',
            'start_date' => $today,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->has('instances', 1)
                ->where('instances.0.status', 'pending'));

        $this->assertDatabaseHas('task_instances', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertEquals(1, \App\Models\TaskInstance::where('user_id', $user->id)
            ->whereDate('scheduled_date', $today)
            ->count());
    }

    public function test_creating_template_generates_today_instance(): void
    {
        $user = User::factory()->create([
            'hp' => 0,
            'xp' => 0,
            'level' => 1,
        ]);
        $today = Carbon::today()->toDateString();

        $this->actingAs($user)
            ->postJson(route('web_api.tasks.store'), [
                'title' => 'Đọc sách',
                'priority' => 'medium',
                'recurrence_type' => 'daily',
                'start_date' => $today,
            ])
            ->assertOk();

        $this->assertDatabaseHas('task_instances', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertEquals(1, \App\Models\TaskInstance::where('user_id', $user->id)
            ->whereDate('scheduled_date', $today)
            ->count());
    }
}
