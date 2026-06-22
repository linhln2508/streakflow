<?php

namespace App\Services;

class GamificationService
{
    public function calculateSkipQuota(int $totalTasks): int
    {
        return (int) floor($totalTasks * 0.25);
    }

    public function calculateHpChange(int $totalTasks, int $skipped, int $skippedAuto): array
    {
        $totalSkipped = $skipped + $skippedAuto;
        $skipQuota = $this->calculateSkipQuota($totalTasks);
        $overSkip = max(0, $totalSkipped - $skipQuota);
        $savedSkip = max(0, $skipQuota - $totalSkipped);
        $hpChange = $savedSkip - $overSkip;

        return [
            'skip_quota' => $skipQuota,
            'over_skip' => $overSkip,
            'saved_skip' => $savedSkip,
            'hp_change' => $hpChange,
        ];
    }

    public function calculateHpAfter(int $hpBefore, int $hpChange): int
    {
        return min(100, $hpBefore + $hpChange);
    }

    public function applyManualHpAdjustment(int $currentHp, int $delta): int
    {
        return min(100, $currentHp + $delta);
    }

    public function calculateCompletionPercent(int $done, int $total): float
    {
        if ($total === 0) {
            return 0.0;
        }

        return round(($done / $total) * 100, 2);
    }

    public function getBaseXp(int $totalTasks): int
    {
        return match (true) {
            $totalTasks <= 4 => 20,
            $totalTasks <= 8 => 35,
            $totalTasks <= 12 => 50,
            $totalTasks <= 16 => 65,
            default => 80,
        };
    }

    public function getPerformanceMultiplier(float $pctCompleted): float
    {
        return match (true) {
            $pctCompleted >= 100 => 2.0,
            $pctCompleted >= 90 => 1.5,
            $pctCompleted >= 75 => 1.0,
            $pctCompleted >= 50 => 0.5,
            default => 0.0,
        };
    }

    public function getStreakBonus(int $streak): float
    {
        return match (true) {
            $streak >= 100 => 0.75,
            $streak >= 60 => 0.50,
            $streak >= 30 => 0.35,
            $streak >= 14 => 0.20,
            $streak >= 7 => 0.10,
            default => 0.0,
        };
    }

    public function calculateXpEarned(
        int $totalTasks,
        float $pctCompleted,
        int $streak,
        int $hpBefore,
        int $hpChange
    ): array {
        $baseXp = $this->getBaseXp($totalTasks);
        $perfMultiplier = $this->getPerformanceMultiplier($pctCompleted);
        $streakBonus = $this->getStreakBonus($streak);
        $streakMultiplier = 1 + $streakBonus;

        $xpEarned = (int) round($baseXp * $perfMultiplier * $streakMultiplier);

        if ($hpBefore >= 100 && $hpChange > 0) {
            $xpEarned += $hpChange * 10;
        }

        return [
            'xp_earned' => $xpEarned,
            'base_xp' => $baseXp,
            'perf_multiplier' => $perfMultiplier,
            'streak_multiplier' => $streakMultiplier,
        ];
    }

    public function calculateLevel(int $xp): int
    {
        $level = 1;

        while ($this->xpRequiredForLevel($level + 1) <= $xp) {
            $level++;
        }

        return $level;
    }

    public function xpRequiredForLevel(int $level): int
    {
        if ($level <= 1) {
            return 0;
        }

        $total = 0;
        for ($n = 1; $n < $level; $n++) {
            $total += 100 * $n * $n + 50 * $n;
        }

        return $total;
    }

    public function xpToNextLevel(int $xp, int $currentLevel): int
    {
        $nextLevelXp = $this->xpRequiredForLevel($currentLevel + 1);

        return max(0, $nextLevelXp - $xp);
    }

    public function processStreak(
        int $streakBefore,
        int $shieldCount,
        int $debtCount,
        float $pctCompleted,
        ?string $strategy = null,
    ): array {
        $result = [
            'streak_after' => $streakBefore,
            'shield_count' => $shieldCount,
            'debt_count' => $debtCount,
            'shield_used' => false,
            'debt_added' => false,
            'debt_cleared' => false,
            'streak_reset' => false,
            'shield_earned' => false,
        ];

        if ($pctCompleted >= 75) {
            $result['streak_after'] = $streakBefore + 1;

            if ($pctCompleted >= 100) {
                $result['shield_count']++;
                $result['shield_earned'] = true;
            }

            if ($debtCount > 0) {
                $result['debt_count'] = 0;
                $result['debt_cleared'] = true;
            }

            return $result;
        }

        if ($strategy === 'reset') {
            return $this->applyStreakReset($result, $streakBefore, $debtCount);
        }

        if ($strategy === 'shield' && $shieldCount > 0) {
            $result['shield_count']--;
            $result['shield_used'] = true;

            return $result;
        }

        if ($strategy === 'debt' && $streakBefore >= 30 && $debtCount === 0) {
            $result['debt_count'] = 1;
            $result['debt_added'] = true;

            return $result;
        }

        if ($strategy === null || $strategy === 'auto') {
            if ($shieldCount > 0) {
                $result['shield_count']--;
                $result['shield_used'] = true;

                return $result;
            }

            if ($streakBefore >= 30) {
                if ($debtCount > 0) {
                    return $this->applyStreakReset($result, $streakBefore, $debtCount);
                }

                $result['debt_count'] = 1;
                $result['debt_added'] = true;

                return $result;
            }

            return $this->applyStreakReset($result, $streakBefore, $debtCount);
        }

        return $this->applyStreakReset($result, $streakBefore, $debtCount);
    }

    protected function applyStreakReset(array $result, int $streakBefore, int $debtCount): array
    {
        $result['streak_after'] = 0;
        $result['streak_reset'] = true;

        if ($debtCount > 0) {
            $result['debt_count'] = 0;
        }

        return $result;
    }

    public function previewCloseDay(
        int $streakBefore,
        int $shieldCount,
        int $debtCount,
        int $total,
        int $done,
        int $skipped,
        int $pending,
    ): array {
        $skippedAuto = $pending;
        $pctCompleted = $this->calculateCompletionPercent($done, $total);
        $hpChange = $this->calculateHpChange($total, $skipped, $skippedAuto)['hp_change'];

        if ($total === 0) {
            return [
                'pct_completed' => 0,
                'hp_change' => 0,
                'needs_streak_choice' => false,
                'default_strategy' => null,
                'outcomes' => [],
            ];
        }

        if ($pctCompleted >= 75) {
            $outcome = $this->processStreak($streakBefore, $shieldCount, $debtCount, $pctCompleted);

            return [
                'pct_completed' => $pctCompleted,
                'hp_change' => $hpChange,
                'needs_streak_choice' => false,
                'default_strategy' => null,
                'outcomes' => [
                    'success' => $this->formatStreakOutcome($outcome, $streakBefore),
                ],
            ];
        }

        $outcomes = [];

        if ($shieldCount > 0) {
            $outcomes['shield'] = $this->formatStreakOutcome(
                $this->processStreak($streakBefore, $shieldCount, $debtCount, $pctCompleted, 'shield'),
                $streakBefore,
            );
        }

        if ($streakBefore >= 30 && $debtCount === 0) {
            $outcomes['debt'] = $this->formatStreakOutcome(
                $this->processStreak($streakBefore, $shieldCount, $debtCount, $pctCompleted, 'debt'),
                $streakBefore,
            );
        }

        $outcomes['reset'] = $this->formatStreakOutcome(
            $this->processStreak($streakBefore, $shieldCount, $debtCount, $pctCompleted, 'reset'),
            $streakBefore,
        );

        $defaultStrategy = match (true) {
            $shieldCount > 0 => 'shield',
            $streakBefore >= 30 && $debtCount === 0 => 'debt',
            default => 'reset',
        };

        return [
            'pct_completed' => $pctCompleted,
            'hp_change' => $hpChange,
            'needs_streak_choice' => true,
            'default_strategy' => $defaultStrategy,
            'shield_count' => $shieldCount,
            'debt_count' => $debtCount,
            'streak_before' => $streakBefore,
            'outcomes' => $outcomes,
        ];
    }

    protected function formatStreakOutcome(array $result, int $streakBefore): array
    {
        return [
            'streak_before' => $streakBefore,
            'streak_after' => $result['streak_after'],
            'shield_used' => $result['shield_used'],
            'shield_earned' => $result['shield_earned'],
            'debt_added' => $result['debt_added'],
            'debt_cleared' => $result['debt_cleared'],
            'streak_reset' => $result['streak_reset'],
            'shield_count_after' => $result['shield_count'],
            'debt_count_after' => $result['debt_count'],
        ];
    }

    public function predictHpChange(int $totalTasks, int $done, int $skipped, int $pending): int
    {
        $skippedAuto = $pending;
        $hp = $this->calculateHpChange($totalTasks, $skipped, $skippedAuto);

        return $hp['hp_change'];
    }
}
