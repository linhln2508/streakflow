<?php

namespace Tests\Unit;

use App\Models\TaskTemplate;
use App\Models\User;
use App\Services\TaskRecurrenceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskRecurrenceServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskRecurrenceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaskRecurrenceService();
    }

    private function createTemplate(array $overrides = []): TaskTemplate
    {
        $user = User::factory()->create();

        return TaskTemplate::create(array_merge([
            'user_id' => $user->id,
            'title' => 'Test',
            'priority' => 'medium',
            'recurrence_type' => 'daily',
            'recurrence_config' => null,
            'start_date' => Carbon::parse('2025-06-01'),
            'end_date' => null,
            'is_active' => true,
        ], $overrides));
    }

    public function test_daily_always_matches(): void
    {
        $template = $this->createTemplate();

        $this->assertTrue(
            $this->service->shouldGenerateForDate($template, Carbon::parse('2025-06-17'))
        );
    }

    public function test_weekdays_only_monday_to_friday(): void
    {
        $template = $this->createTemplate(['recurrence_type' => 'weekdays']);

        $this->assertTrue($this->service->shouldGenerateForDate($template, Carbon::parse('2025-06-16')));
        $this->assertFalse($this->service->shouldGenerateForDate($template, Carbon::parse('2025-06-15')));
    }

    public function test_weekly_matches_configured_days(): void
    {
        $template = $this->createTemplate([
            'recurrence_type' => 'weekly',
            'recurrence_config' => ['days' => [1, 3, 5]],
        ]);

        $this->assertTrue($this->service->shouldGenerateForDate($template, Carbon::parse('2025-06-16')));
        $this->assertFalse($this->service->shouldGenerateForDate($template, Carbon::parse('2025-06-17')));
    }

    public function test_custom_interval(): void
    {
        $template = $this->createTemplate([
            'recurrence_type' => 'custom',
            'recurrence_config' => ['interval' => 3],
            'start_date' => Carbon::parse('2025-06-01'),
        ]);

        $this->assertTrue($this->service->shouldGenerateForDate($template, Carbon::parse('2025-06-01')));
        $this->assertFalse($this->service->shouldGenerateForDate($template, Carbon::parse('2025-06-02')));
        $this->assertTrue($this->service->shouldGenerateForDate($template, Carbon::parse('2025-06-04')));
    }

    public function test_one_time_only_on_specific_date(): void
    {
        $template = $this->createTemplate([
            'recurrence_type' => 'one_time',
            'recurrence_config' => ['date' => '2025-06-20'],
            'start_date' => Carbon::parse('2025-06-20'),
        ]);

        $this->assertTrue($this->service->shouldGenerateForDate($template, Carbon::parse('2025-06-20')));
        $this->assertFalse($this->service->shouldGenerateForDate($template, Carbon::parse('2025-06-21')));
    }
}
