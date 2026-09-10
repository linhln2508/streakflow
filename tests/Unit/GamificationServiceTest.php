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

    public function test_debt_strategy_when_explicit(): void
    {
        $result = $this->service->processStreak(5, 2, 0, 50.0, 'debt');
        $this->assertEquals(5, $result['streak_after']);
        $this->assertTrue($result['debt_added']);
        $this->assertEquals(2, $result['shield_count']);
        $this->assertFalse($result['shield_used']);
    }

    public function test_debt_not_allowed_when_already_in_debt(): void
    {
        $result = $this->service->processStreak(5, 0, 1, 50.0, 'debt');
        $this->assertEquals(0, $result['streak_after']);
        $this->assertTrue($result['streak_reset']);
        $this->assertFalse($result['debt_added']);
    }

    public function test_auto_uses_debt_without_streak_requirement(): void
    {
        $result = $this->service->processStreak(3, 0, 0, 50.0, 'auto');
        $this->assertEquals(3, $result['streak_after']);
        $this->assertTrue($result['debt_added']);
        $this->assertEquals(1, $result['debt_count']);
    }

    public function test_reset_strategy_ignores_shield(): void
    {
        $result = $this->service->processStreak(10, 2, 0, 50.0, 'reset');
        $this->assertEquals(0, $result['streak_after']);
        $this->assertTrue($result['streak_reset']);
        $this->assertEquals(2, $result['shield_count']);
    }

    public function test_preview_close_day_offers_shield_and_debt(): void
    {
        $preview = $this->service->previewCloseDay(5, 1, 0, 10, 2, 1, 7);
        $this->assertTrue($preview['needs_streak_choice']);
        $this->assertArrayHasKey('shield', $preview['outcomes']);
        $this->assertArrayHasKey('debt', $preview['outcomes']);
        $this->assertEquals('shield', $preview['default_strategy']);
    }

    public function test_preview_offers_debt_when_no_shield_and_no_debt(): void
    {
        $preview = $this->service->previewCloseDay(2, 0, 0, 10, 2, 1, 7);
        $this->assertArrayHasKey('debt', $preview['outcomes']);
        $this->assertEquals('debt', $preview['default_strategy']);
    }

    public function test_preview_hides_debt_when_already_in_debt(): void
    {
        $preview = $this->service->previewCloseDay(10, 0, 1, 10, 2, 1, 7);
        $this->assertArrayNotHasKey('debt', $preview['outcomes']);
        $this->assertEquals('reset', $preview['default_strategy']);
    }
}
