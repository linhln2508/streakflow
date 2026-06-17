<?php

namespace Tests\Unit;

use App\Services\GamificationService;
use PHPUnit\Framework\TestCase;

class GamificationServiceTest extends TestCase
{
    private GamificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GamificationService();
    }

    public function test_hp_change_with_no_skips(): void
    {
        $result = $this->service->calculateHpChange(12, 0, 0);
        $this->assertEquals(3, $result['skip_quota']);
        $this->assertEquals(3, $result['saved_skip']);
        $this->assertEquals(3, $result['hp_change']);
    }

    public function test_hp_change_with_over_skip(): void
    {
        $result = $this->service->calculateHpChange(12, 3, 2);
        $this->assertEquals(2, $result['over_skip']);
        $this->assertEquals(-2, $result['hp_change']);
    }

    public function test_streak_increments_at_75_percent(): void
    {
        $result = $this->service->processStreak(5, 0, 0, 80.0);
        $this->assertEquals(6, $result['streak_after']);
    }

    public function test_shield_used_when_below_75(): void
    {
        $result = $this->service->processStreak(10, 2, 0, 50.0);
        $this->assertEquals(10, $result['streak_after']);
        $this->assertTrue($result['shield_used']);
        $this->assertEquals(1, $result['shield_count']);
    }

    public function test_level_calculation(): void
    {
        $this->assertEquals(1, $this->service->calculateLevel(0));
        $this->assertEquals(2, $this->service->calculateLevel(150));
        $this->assertEquals(3, $this->service->calculateLevel(650));
    }
}
